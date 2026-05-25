<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business_locations;
use App\Models\ItemHistory;
use App\Models\LocationProductList;
use App\Models\Product_list;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    /**
     * Display a paginated, filtered, and searchable list of products.
     * 
     * This method handles server-side pagination, search, filtering, and sorting
     * for the product catalog. It supports case-insensitive search across multiple
     * fields and returns encrypted IDs for secure frontend operations.
     *
     * Query Parameters:
     * - page: int (default: 1) - Current page number
     * - per_page: int (default: 50, max: 100) - Number of items per page
     * - search: string (optional) - Case-insensitive search term for name, SKU, and description
     * - status: 'all'|'active'|'inactive' (default: 'all') - Filter by product active status
     * - stock: 'all'|'in_stock'|'low_stock'|'out_of_stock' (default: 'all') - Filter by stock level
     * - category: string|int (optional) - Filter by category ID
     * - price_min: numeric (optional) - Minimum price filter
     * - price_max: numeric (optional) - Maximum price filter
     * - sort_by: 'name'|'price'|'stock_quantity'|'created_at' (default: 'name') - Sort field
     * - sort_order: 'asc'|'desc' (default: 'asc') - Sort direction
     *
     * @param Request $request The incoming HTTP request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // ==========================================
        // STEP 1: AUTHENTICATION VERIFICATION
        // ==========================================

        // Get the currently authenticated user from the request
        // Auth::user() returns null if no user is authenticated
        $user = Auth::user();

        // Return 401 Unauthorized if user is not authenticated
        // This prevents unauthorized access to product data
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // ==========================================
        // STEP 2: BUSINESS CONTEXT VALIDATION
        // ==========================================

        // Check if the user has an active business selected
        // active_business_key is required to scope products to a specific business
        // This ensures multi-tenancy isolation
        if (empty($user->active_business_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 400);
        }

        // ==========================================
        // STEP 3: PAGINATION CONFIGURATION
        // ==========================================

        // Get the requested number of items per page (default: 50)
        // This allows the frontend to control page size
        $perPage = $request->get('per_page', 50);

        // Cap the maximum page size at 100 items
        // This prevents performance issues and abuse (e.g., requesting 10,000 items at once)
        $perPage = min($perPage, 100);

        // ==========================================
        // STEP 4: BASE QUERY SETUP
        // ==========================================

        // Start building the database query
        // Scoped to the user's active business to ensure data isolation
        // All subsequent where clauses will be added to this base query
        $query = Product_list::where('business_key', $user->active_business_key);

        // ==========================================
        // STEP 5: SEARCH FUNCTIONALITY
        // ==========================================

        // Apply search filter only if a search term is provided
        // Uses "has" to check if the key exists and is not empty
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            // Group search conditions in a where clause
            // The function groups all OR conditions together
            // This prevents search OR conditions from breaking other AND filters
            $query->where(function ($q) use ($search) {
                // PostgreSQL ILIKE is case-insensitive by default
                //
                // The % wildcards allow partial matching:
                //   - "%search%" matches any string containing "search"
                //   - "%search" matches strings ending with "search"
                //   - "search%" matches strings starting with "search"
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('sku', 'ILIKE', "%{$search}%")
                    ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // ==========================================
        // STEP 6: STATUS FILTER
        // ==========================================

        // Filter products by their active/inactive status
        // 'all' = no filter applied (default behavior)
        // 'active' = only show products where is_active is true
        // 'inactive' = only show products where is_active is false
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // ==========================================
        // STEP 7: STOCK LEVEL FILTER
        // ==========================================

        // Filter products by their stock availability status
        // This allows users to quickly find products that need attention
        if ($request->has('stock') && $request->stock !== 'all') {
            if ($request->stock === 'in_stock') {
                // Products that are not marked as out of stock AND have quantity > 0
                // Both conditions must be true for a product to be "in stock"
                $query->where('is_out_of_stock', false)
                    ->where('stock_quantity', '>', 0);
            } elseif ($request->stock === 'low_stock') {
                // Products that are not out of stock but have quantity at or below threshold
                // COALESCE(low_stock_threshold, 5) means:
                //   - Use low_stock_threshold value if it's set
                //   - Otherwise use 5 as the default threshold
                // This is a database-level function that handles NULL values
                $query->where('is_out_of_stock', false)
                    ->where('stock_quantity', '>', 0)
                    ->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 5)');
            } elseif ($request->stock === 'out_of_stock') {
                // Products that are either:
                //   - Manually marked as out of stock, OR
                //   - Have stock_quantity of 0 or less (auto-detected)
                // The where(function) groups these OR conditions together
                $query->where(function ($q) {
                    $q->where('is_out_of_stock', true)
                        ->orWhere('stock_quantity', '<=', 0);
                });
            }
        }

        // ==========================================
        // STEP 8: CATEGORY FILTER
        // ==========================================

        // Filter products by category ID
        // Validates that:
        //   1. Category parameter exists
        //   2. Category is not set to 'all' (meaning no filter)
        //   3. Category value is numeric (prevents SQL injection attempts)
        if ($request->has('category') && $request->category !== 'all' && is_numeric($request->category)) {
            $query->where('category_id', $request->category);
        }

        // ==========================================
        // STEP 9: PRICE RANGE FILTER
        // ==========================================

        // Apply minimum price filter
        // Products with price >= price_min will be included
        // Validates that price_min is present and numeric
        if ($request->has('price_min') && is_numeric($request->price_min)) {
            $query->where('price', '>=', $request->price_min);
        }

        // Apply maximum price filter
        // Products with price <= price_max will be included
        // Validates that price_max is present and numeric
        if ($request->has('price_max') && is_numeric($request->price_max)) {
            $query->where('price', '<=', $request->price_max);
        }

        // ==========================================
        // STEP 10: SORTING
        // ==========================================

        // Get sort preferences from request (defaults: sort by name, ascending)
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        // Whitelist of allowed sort fields for security
        // This prevents SQL injection through the sort parameter
        // Only these specific columns can be used for sorting
        $allowedSortFields = ['name', 'price', 'stock_quantity', 'created_at'];

        // Apply sorting only if the requested field is in the whitelist
        if (in_array($sortBy, $allowedSortFields)) {
            // Convert sort order to either 'asc' or 'desc'
            // If any value other than 'desc' is provided, default to 'asc'
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        // ==========================================
        // STEP 11: EAGER LOAD RELATIONSHIPS
        // ==========================================

        // Eager load the category relationship to prevent N+1 query problem
        // Only load id and name columns to optimize performance
        // Without this, each product would trigger a separate query for its category
        $query->with(['category:id,name']);

        // ==========================================
        // STEP 12: EXECUTE PAGINATED QUERY
        // ==========================================

        // Execute the query with pagination
        // This returns a LengthAwarePaginator instance containing:
        //   - The paginated results
        //   - Pagination metadata (total count, current page, etc.)
        $products = $query->paginate($perPage);

        // ==========================================
        // STEP 13: ENCRYPT PRODUCT IDs
        // ==========================================

        // Transform each product in the current page's collection
        // getCollection() gets the items for the current page
        // transform() modifies each item in-place
        $products->getCollection()->transform(function ($product) {
            // Add an encrypted version of the product ID
            // This is used by the frontend for secure operations
            // Prevents exposing raw database IDs if needed for security
            $product->encrypted_id = ($product->id);
            return $product;
        });

        // ==========================================
        // STEP 14: RETURN JSON RESPONSE
        // ==========================================

        // Return a standardized JSON response with:
        //   - success flag for easy frontend checking
        //   - data containing the current page's products (with encrypted IDs)
        //   - pagination metadata for frontend pagination controls
        return response()->json([
            'success' => true,
            'data' => $products->items(),  // Returns only the current page's items
            'pagination' => [
                'current_page' => $products->currentPage(),    // Current page number (e.g., 3)
                'last_page' => $products->lastPage(),          // Total number of pages (e.g., 10)
                'per_page' => $products->perPage(),            // Items per page (e.g., 50)
                'total' => $products->total(),                 // Total items across all pages (e.g., 500)
                'next_page_url' => $products->nextPageUrl(),   // URL for next page (null if on last page)
                'prev_page_url' => $products->previousPageUrl(), // URL for previous page (null if on first page)
            ]
        ]);
    }



    // public function locationproducts(Request $request, $id)
    // {
    //     $user = Auth::user();

    //     Log::info('LocationProducts - Request started', [
    //         'user_id' => $user->id ?? 'not authenticated',
    //         'location_id' => $id,
    //         'params' => $request->all()
    //     ]);

    //     if (!$user) {
    //         Log::warning('LocationProducts - Unauthenticated');
    //         return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    //     }

    //     if (empty($user->active_business_key)) {
    //         Log::warning('LocationProducts - No active business', ['user_id' => $user->id]);
    //         return response()->json(['success' => false, 'message' => 'No active business selected'], 400);
    //     }

    //     if (!$user->hasPermission('locations_read')) {
    //         Log::warning('LocationProducts - Permission denied', ['user_id' => $user->id]);
    //         return response()->json(['success' => false, 'message' => 'Feature Unavailable.'], 403);
    //     }

    //     $request->validate([
    //         'price_min' => 'nullable|numeric|min:0',
    //         'price_max' => 'nullable|numeric|min:0',
    //         'per_page'  => 'nullable|integer|min:1|max:100',
    //         'category'  => 'nullable|string|exists:product_categories,id',
    //     ]);

    //     $locationId = $id;

    //     $location = Business_locations::where('id', $locationId)
    //         ->where('business_key', $user->active_business_key)
    //         ->first();

    //     if (!$location) {
    //         Log::warning('LocationProducts - Location not found', [
    //             'location_id' => $locationId,
    //             'business_key' => $user->active_business_key
    //         ]);
    //         return response()->json(['success' => false, 'message' => 'Location not found'], 404);
    //     }

    //     Log::info('LocationProducts - Location found', [
    //         'location_id' => $location->id,
    //         'location_name' => $location->location_name
    //     ]);

    //     $perPage = (int) $request->input('per_page', 15);
    //     $perPage = min(max($perPage, 1), 100);

    //     try {
    //         $query = LocationProductList::with([
    //             'product:id,name,slug,description,sku,dimensions,discount_percentage,discount_start_date,discount_end_date,manufactured_at,expires_at,weight,length,width,height,is_active,is_featured,is_on_sale,is_out_of_stock,image,additional_info,barcode',
    //             'category:id,name'
    //         ])
    //             ->where('business_key', $user->active_business_key)
    //             ->where('location_id', $locationId);

    //         // Log the base query SQL for debugging
    //         Log::info('LocationProducts - Base query built', [
    //             'sql' => $query->toSql(),
    //             'bindings' => $query->getBindings()
    //         ]);

    //         // ---- Search ----
    //         if ($search = $request->input('search')) {
    //             Log::info('LocationProducts - Search filter applied', ['search' => $search]);
    //             $query->whereHas('product', function ($q) use ($search) {
    //                 $q->where('name', 'ilike', "%{$search}%")
    //                     ->orWhere('sku', 'ilike', "%{$search}%")
    //                     ->orWhere('description', 'ilike', "%{$search}%");
    //             });
    //         }

    //         // ---- Status filter ----
    //         if (($status = $request->input('status')) && in_array($status, ['active', 'inactive'])) {
    //             Log::info('LocationProducts - Status filter applied', ['status' => $status]);
    //             $query->whereHas('product', function ($q) use ($status) {
    //                 $q->where('is_active', $status === 'active');
    //             });
    //         }

    //         // ---- Category filter ----
    //         if ($categoryId = $request->input('category')) {
    //             Log::info('LocationProducts - Category filter applied', ['category_id' => $categoryId]);
    //             $query->whereHas('product', function ($q) use ($categoryId) {
    //                 $q->where('category_id', $categoryId);
    //             });
    //         }

    //         // ---- Stock level filter ----
    //         $stock = $request->input('stock');
    //         if ($stock) {
    //             Log::info('LocationProducts - Stock filter applied', ['stock' => $stock]);
    //         }
    //         if ($stock === 'in_stock') {
    //             $query->where('stock_quantity', '>', 0)
    //                 ->whereHas('product', function ($q) {
    //                     $q->where('is_out_of_stock', false);
    //                 });
    //         } elseif ($stock === 'low_stock') {
    //             $query->where('stock_quantity', '>', 0)
    //                 ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
    //                 ->whereHas('product', function ($q) {
    //                     $q->where('is_out_of_stock', false);
    //                 });
    //         } elseif ($stock === 'out_of_stock') {
    //             $query->where(function ($q) {
    //                 $q->where('stock_quantity', '<=', 0)
    //                     ->orWhereHas('product', function ($q2) {
    //                         $q2->where('is_out_of_stock', true);
    //                     });
    //             });
    //         }

    //         // ---- Price range ----
    //         if ($priceMin = $request->input('price_min')) {
    //             Log::info('LocationProducts - Price min filter', ['price_min' => $priceMin]);
    //             $query->where('price', '>=', (float) $priceMin);
    //         }
    //         if ($priceMax = $request->input('price_max')) {
    //             Log::info('LocationProducts - Price max filter', ['price_max' => $priceMax]);
    //             $query->where('price', '<=', (float) $priceMax);
    //         }

    //         // ---- Sorting ----
    //         $sortBy = $request->input('sort_by', 'created');
    //         $sortOrder = strtolower($request->input('sort_order', 'desc'));
    //         $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

    //         Log::info('LocationProducts - Sorting', ['sort_by' => $sortBy, 'sort_order' => $sortOrder]);

    //         switch ($sortBy) {
    //             case 'name':
    //                 $query->orderByRaw(
    //                     '(SELECT name FROM product_lists WHERE product_lists.id = location_product_lists.product_id LIMIT 1) ' . $sortOrder
    //                 );
    //                 break;
    //             case 'price':
    //                 $query->orderBy('price', $sortOrder);
    //                 break;
    //             case 'stock':
    //                 $query->orderBy('stock_quantity', $sortOrder);
    //                 break;
    //             case 'created':
    //                 $query->orderBy('created_at', $sortOrder);
    //                 break;
    //             default:
    //                 $query->orderBy('created_at', 'desc');
    //         }

    //         // Calculate total inventory value
    //         $totalValue = (clone $query)->sum(DB::raw('price * stock_quantity'));

    //         Log::info('LocationProducts - Total inventory value', ['total_value' => $totalValue]);

    //         // Get count before pagination
    //         $totalCount = (clone $query)->count();
    //         Log::info('LocationProducts - Total records before pagination', ['count' => $totalCount]);

    //         $paginator = $query->paginate($perPage);

    //         Log::info('LocationProducts - Pagination results', [
    //             'current_page' => $paginator->currentPage(),
    //             'total_items' => $paginator->total(),
    //             'items_in_page' => $paginator->count(),
    //             'has_more_pages' => $paginator->hasMorePages()
    //         ]);

    //         // Log first item for debugging product relationship
    //         $firstItem = $paginator->items()[0] ?? null;
    //         if ($firstItem) {
    //             Log::info('LocationProducts - First item details', [
    //                 'has_product_relation' => $firstItem->relationLoaded('product'),
    //                 'product_exists' => $firstItem->product ? 'yes' : 'no',
    //                 'product_data' => $firstItem->product ? [
    //                     'id' => $firstItem->product->id,
    //                     'name' => $firstItem->product->name,
    //                     'sku' => $firstItem->product->sku,
    //                 ] : null,
    //                 'raw_item' => $firstItem->toArray()
    //             ]);
    //         }

    //         $paginator->getCollection()->transform(function ($item) use ($location) {
    //             $item->encrypted_id = $item->id;
    //             $item->encrypted_pid = $item->product_id;
    //             $item->encrypted_location_id = $item->location_id;
    //             $item->location_name = $location->location_name;

    //             if ($item->product) {
    //                 $item->product_name = $item->product->name; // Explicitly add product_name
    //                 $item->is_active = $item->product->is_active;
    //                 $item->is_out_of_stock = $item->product->is_out_of_stock;
    //                 $item->is_featured = $item->product->is_featured;
    //                 $item->is_on_sale = $item->product->is_on_sale;

    //                 Log::info('LocationProducts - Item transformed', [
    //                     'product_id' => $item->product_id,
    //                     'product_name' => $item->product->name,
    //                     'has_name' => !empty($item->product->name)
    //                 ]);
    //             } else {
    //                 Log::warning('LocationProducts - Product missing for item', [
    //                     'location_product_id' => $item->id,
    //                     'product_id' => $item->product_id,
    //                     'location_id' => $item->location_id
    //                 ]);

    //                 $item->product_name = 'Unknown Product';
    //                 $item->is_active = false;
    //                 $item->is_out_of_stock = false;
    //                 $item->is_featured = false;
    //                 $item->is_on_sale = false;
    //             }

    //             return $item;
    //         });

    //         // Log final data sample
    //         $finalItems = $paginator->items();
    //         if (!empty($finalItems)) {
    //             Log::info('LocationProducts - Final response sample', [
    //                 'first_item_keys' => array_keys((array)$finalItems[0]),
    //                 'has_product_name' => isset($finalItems[0]->product_name),
    //                 'sample_product_name' => $finalItems[0]->product_name ?? 'NOT SET'
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => $paginator->items(),
    //             'location_name' => $location->location_name,
    //             'pagination' => [
    //                 'current_page' => $paginator->currentPage(),
    //                 'last_page' => $paginator->lastPage(),
    //                 'per_page' => $paginator->perPage(),
    //                 'total' => $paginator->total(),
    //                 'total_value' => $totalValue ?? 0,
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('LocationProducts Error', [
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString(),
    //             'params' => $request->all(),
    //             'location_id' => $id,
    //             'business_key' => $user->active_business_key ?? 'not set',
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }



    public function locationproducts(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        if (empty($user->active_business_key)) {
            return response()->json(['success' => false, 'message' => 'No active business selected'], 400);
        }

        if (!$user->hasPermission('locations_read')) {
            return response()->json(['success' => false, 'message' => 'Feature Unavailable.'], 403);
        }

        $request->validate([
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'category'  => 'nullable|string|exists:product_categories,id',
        ]);

        $locationId = $id;

        $location = Business_locations::where('id', $locationId)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$location) {
            return response()->json(['success' => false, 'message' => 'Location not found'], 404);
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        try {
            $query = LocationProductList::with([
                'product:id,name,slug,description,sku,dimensions,discount_percentage,discount_start_date,discount_end_date,manufactured_at,expires_at,weight,length,width,height,is_active,is_featured,is_on_sale,is_out_of_stock,image,additional_info,barcode',
                'category:id,name'
            ])
                ->where('business_key', $user->active_business_key)
                ->where('location_id', $locationId);

            // ---- Search ----
            if ($search = $request->input('search')) {
                $query->whereHas('product', function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('sku', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            }

            // ---- Status filter ----
            if (($status = $request->input('status')) && in_array($status, ['active', 'inactive'])) {
                $query->whereHas('product', function ($q) use ($status) {
                    $q->where('is_active', $status === 'active');
                });
            }

            // ---- Category filter ----
            if ($categoryId = $request->input('category')) {
                $query->whereHas('product', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            // ---- Stock level filter ----
            $stock = $request->input('stock');
            if ($stock === 'in_stock') {
                $query->where('stock_quantity', '>', 0)
                    ->whereHas('product', function ($q) {
                        $q->where('is_out_of_stock', false);
                    });
            } elseif ($stock === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                    ->whereHas('product', function ($q) {
                        $q->where('is_out_of_stock', false);
                    });
            } elseif ($stock === 'out_of_stock') {
                $query->where(function ($q) {
                    $q->where('stock_quantity', '<=', 0)
                        ->orWhereHas('product', function ($q2) {
                            $q2->where('is_out_of_stock', true);
                        });
                });
            }

            // ---- Price range ----
            if ($priceMin = $request->input('price_min')) {
                $query->where('price', '>=', (float) $priceMin);
            }
            if ($priceMax = $request->input('price_max')) {
                $query->where('price', '<=', (float) $priceMax);
            }

            // ---- Sorting ----
            $sortBy = $request->input('sort_by', 'created');
            $sortOrder = strtolower($request->input('sort_order', 'desc'));
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

            switch ($sortBy) {
                case 'name':
                    $query->orderByRaw(
                        '(SELECT name FROM product_lists WHERE product_lists.id = location_product_lists.product_id LIMIT 1) ' . $sortOrder
                    );
                    break;
                case 'price':
                    $query->orderBy('price', $sortOrder);
                    break;
                case 'stock':
                    $query->orderBy('stock_quantity', $sortOrder);
                    break;
                case 'created':
                    $query->orderBy('created_at', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // Calculate total inventory value
            $totalValue = (clone $query)->sum(DB::raw('price * stock_quantity'));

            $paginator = $query->paginate($perPage);

            $paginator->getCollection()->transform(function ($item) use ($location) {
                $item->encrypted_id = $item->id;
                $item->encrypted_pid = $item->product_id;
                $item->encrypted_location_id = $item->location_id;
                $item->location_name = $location->location_name;

                if ($item->product) {
                    $item->product_name = $item->product->name;
                    $item->is_active = $item->product->is_active;
                    $item->is_out_of_stock = $item->product->is_out_of_stock;
                    $item->is_featured = $item->product->is_featured;
                    $item->is_on_sale = $item->product->is_on_sale;
                } else {
                    $item->product_name = 'Unknown Product';
                    $item->is_active = false;
                    $item->is_out_of_stock = false;
                    $item->is_featured = false;
                    $item->is_on_sale = false;
                }

                return $item;
            });

            $responseData = [
                'success' => true,
                'data' => $paginator->items(),
                'location_name' => $location->location_name,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'total_value' => $totalValue ?? 0,
                ]
            ];

            Log::info('LocationProducts - Response payload', [
                'user_id' => $user->id,
                'location_id' => $locationId,
                'request_params' => $request->except(['password', 'token']),
                'response' => $responseData
            ]);

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('LocationProducts Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'params' => $request->all(),
                'location_id' => $id,
                'business_key' => $user->active_business_key ?? 'not set',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function distributeProducts(Request $request)
    {
        try {
            $ownerId = Auth::id();
            $businessKey = Auth::user()->active_business_key;

            if (!$businessKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active business key not found for user'
                ], 403);
            }

            $validated = $request->validate([
                'destination_location_id' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($businessKey) {
                        $location = Business_locations::where('id', $value)
                            ->where('business_key', $businessKey)
                            ->exists();
                        if (!$location) {
                            $fail('The selected location does not belong to your business.');
                        }
                    },
                ],
                'items' => 'required|array|min:1',
                'items.*.product_id' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($businessKey) {
                        $product = Product_list::where('id', $value)
                            ->where('business_key', $businessKey)
                            ->exists();
                        if (!$product) {
                            $fail('The selected product does not belong to your business.');
                        }
                    },
                ],
                'items.*.quantity' => 'required|integer|min:0',
                'notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $destinationLocationId = $request->destination_location_id;
            $items = $request->items;
            $notes = $request->notes;

            $destinationLocation = Business_locations::where('id', $destinationLocationId)
                ->where('business_key', $businessKey)
                ->first();

            if (!$destinationLocation) {
                throw new \Exception("Location not found or doesn't belong to business");
            }

            $processedItems = [];
            $totalItems = 0;
            $errors = [];

            foreach ($items as $item) {
                try {
                    $productId = $item['product_id'];
                    $quantity = (int)$item['quantity'];

                    $product = Product_list::where('id', $productId)
                        ->where('business_key', $businessKey)
                        ->first();

                    if (!$product) {
                        $errors[] = "Product ID {$productId} not found or doesn't belong to business";
                        continue;
                    }

                    // Check if product already exists at this location
                    $existingProduct = LocationProductList::where('location_id', $destinationLocationId)
                        ->where('product_id', $productId)
                        ->where('business_key', $businessKey)
                        ->first();

                    if ($existingProduct) {
                        // Update existing record - add to existing stock
                        $newStock = (int)($existingProduct->stock_quantity + $quantity);
                        $existingProduct->update([
                            'stock_quantity' => $newStock,
                        ]);
                        $locationProduct = $existingProduct;
                    } else {
                        // Calculate low stock threshold
                        $lowStockThreshold = 0;
                        if ($product->low_stock_threshold !== null) {
                            $lowStockThreshold = is_string($product->low_stock_threshold)
                                ? (int)round((float)$product->low_stock_threshold)
                                : (int)$product->low_stock_threshold;
                        }

                        // Create new location product record
                        $locationProduct = LocationProductList::create([
                            'owner_id' => $ownerId,
                            'business_key' => $businessKey,
                            'location_id' => $destinationLocationId,
                            'product_id' => $productId,
                            'category_id' => $product->category_id,
                            'supplier_id' => $product->supplier_id,
                            'price' => (float)($product->price ?? 0),
                            'cost_price' => $product->cost_price ? (float)$product->cost_price : null,
                            'sale_price' => $product->sale_price ? (float)$product->sale_price : null,
                            'stock_quantity' => $quantity,
                            'low_stock_threshold' => $lowStockThreshold,
                        ]);
                    }

                    // Create item history record
                    ItemHistory::create([
                        'product_id' => $productId,
                        'owner_id' => $ownerId,
                        'business_key' => $businessKey,
                        'location_id' => $destinationLocationId,
                        'from_branch_id' => null, // No branch transfer happening
                        'to_branch_id' => $destinationLocationId,
                        'type' => $quantity > 0 ? 'Newly Added' : 'linked',
                        'quantity' => $quantity,
                        'cost' => $product->cost_price,
                        'price' => $product->price,
                        'source_id' => 1, // morphs creates bigint, use a numeric default
                        'source_type' => 'admin',
                        'note' => $notes ?? "Newly Added From Product Catalog / Head Office",
                        'transaction_date' => now(),
                    ]);

                    $processedItems[] = [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'new_stock' => $locationProduct->stock_quantity,
                    ];

                    if ($quantity > 0) {
                        $totalItems += $quantity;
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing distribution item', [
                        'product_id' => $item['product_id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Error processing product: {$e->getMessage()}";
                    continue;
                }
            }

            if (empty($processedItems)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No items could be processed',
                    'errors' => $errors
                ], 422);
            }

            DB::commit();

            $locationName = $destinationLocation->location_name ?? 'Unknown Location';
            $message = $totalItems > 0
                ? "Successfully added {$totalItems} units to {$locationName}"
                : count($processedItems) . " products added to {$locationName}";

            if (!empty($errors)) {
                $message .= ". Some items had errors.";
                Log::warning('Distribution completed with errors', ['errors' => $errors]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'warnings' => !empty($errors) ? $errors : null,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Distribution failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to distribute products',
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred'
            ], 500);
        }
    }


    // public function distributeProducts(Request $request)
    // {
    //     try {
    //         // Get the ULID of the authenticated user
    //         $ownerId = Auth::id(); // This will return the ULID string
    //         $businessKey = Auth::user()->active_business_key;

    //         // Log the owner ID to verify it's a ULID
    //         Log::info('Distribution request - Auth info', [
    //             'owner_id' => $ownerId,
    //             'owner_id_type' => gettype($ownerId),
    //             'business_key' => $businessKey,
    //         ]);

    //         // Log the incoming request for debugging
    //         Log::info('Distribution request received', [
    //             'user_id' => $ownerId,
    //             'business_key' => $businessKey,
    //             'request_data' => $request->all(),
    //             'destination_location_id' => $request->destination_location_id,
    //             'destination_location_id_type' => gettype($request->destination_location_id),
    //             'items_count' => is_array($request->items) ? count($request->items) : 0,
    //         ]);

    //         $validated = $request->validate([
    //             'destination_location_id' => [
    //                 'required',
    //                 'string',
    //                 function ($attribute, $value, $fail) use ($businessKey) {
    //                     $location = Business_locations::where('id', $value)
    //                         ->where('business_key', $businessKey)
    //                         ->exists();
    //                     if (!$location) {
    //                         Log::warning('Location validation failed', [
    //                             'location_id' => $value,
    //                             'business_key' => $businessKey
    //                         ]);
    //                         $fail('The selected location does not belong to your business.');
    //                     }
    //                 },
    //             ],
    //             'items' => 'required|array|min:1',
    //             'items.*.product_id' => [
    //                 'required',
    //                 'string',
    //                 function ($attribute, $value, $fail) use ($businessKey) {
    //                     $product = Product_list::where('id', $value)
    //                         ->where('business_key', $businessKey)
    //                         ->exists();

    //                     if (!$product) {
    //                         Log::warning('Product validation failed', [
    //                             'product_id' => $value,
    //                             'business_key' => $businessKey
    //                         ]);
    //                         $fail('The selected product does not belong to your business.');
    //                     }
    //                 },
    //             ],
    //             'items.*.quantity' => 'required|integer|min:0',
    //             'notes' => 'nullable|string|max:500',
    //         ]);

    //         DB::beginTransaction();
    //         Log::info('Transaction started');

    //         if (!$businessKey) {
    //             throw new \Exception('Active business key not found for user');
    //         }

    //         // Keep as string - ULID
    //         $destinationLocationId = $request->destination_location_id;
    //         $items = $request->items;
    //         $notes = $request->notes ?? null;

    //         Log::info('Processing distribution', [
    //             'destination_location_id' => $destinationLocationId,
    //             'items_count' => count($items)
    //         ]);

    //         $destinationLocation = Business_locations::where('id', $destinationLocationId)
    //             ->where('business_key', $businessKey)
    //             ->first();

    //         if (!$destinationLocation) {
    //             Log::error('Destination location not found', [
    //                 'location_id' => $destinationLocationId,
    //                 'business_key' => $businessKey
    //             ]);
    //             throw new \Exception("Location not found or doesn't belong to business");
    //         }

    //         Log::info('Destination location found', [
    //             'location_id' => $destinationLocation->id,
    //             'location_name' => $destinationLocation->location_name ?? $destinationLocation->name
    //         ]);

    //         $processedItems = [];
    //         $totalItems = 0;
    //         $errors = [];

    //         foreach ($items as $index => $item) {
    //             try {
    //                 // Keep as string - ULID
    //                 $productId = $item['product_id'];
    //                 $quantity = (int)$item['quantity'];

    //                 Log::info('Processing item', [
    //                     'index' => $index,
    //                     'product_id' => $productId,
    //                     'product_id_type' => gettype($productId),
    //                     'quantity' => $quantity
    //                 ]);

    //                 $product = Product_list::where('id', $productId)
    //                     ->where('business_key', $businessKey)
    //                     ->first();

    //                 if (!$product) {
    //                     $errorMsg = "Product ID {$productId} not found or doesn't belong to business";
    //                     Log::error($errorMsg, [
    //                         'product_id' => $productId,
    //                         'business_key' => $businessKey
    //                     ]);
    //                     $errors[] = $errorMsg;
    //                     continue; // Skip this item
    //                 }

    //                 Log::info('Product found', [
    //                     'product_id' => $product->id,
    //                     'product_name' => $product->name
    //                 ]);

    //                 // Check if product already exists at this location
    //                 $existingProduct = LocationProductList::where('location_id', $destinationLocationId)
    //                     ->where('product_id', $productId)
    //                     ->where('business_key', $businessKey)
    //                     ->first();

    //                 if ($existingProduct) {
    //                     Log::info('Product exists at location, updating stock', [
    //                         'product_id' => $productId,
    //                         'current_stock' => $existingProduct->stock_quantity,
    //                         'adding' => $quantity
    //                     ]);

    //                     $newStock = (int)($existingProduct->stock_quantity + $quantity);
    //                     $existingProduct->update([
    //                         'stock_quantity' => $newStock,
    //                     ]);

    //                     $locationProduct = $existingProduct;

    //                     Log::info('Stock updated', ['new_stock' => $newStock]);
    //                 } else {
    //                     Log::info('Creating new location product', [
    //                         'product_id' => $productId,
    //                         'quantity' => $quantity
    //                     ]);

    //                     // Calculate low stock threshold
    //                     $lowStockThreshold = 0;
    //                     if ($product->low_stock_threshold !== null) {
    //                         if (is_string($product->low_stock_threshold)) {
    //                             $lowStockThreshold = (int)round((float)$product->low_stock_threshold);
    //                         } else {
    //                             $lowStockThreshold = (int)$product->low_stock_threshold;
    //                         }
    //                     }

    //                     // Create new record - ALL IDs should be strings (ULIDs)
    //                     $locationProductData = [
    //                         'owner_id' => $ownerId, // ULID string - DON'T cast to int
    //                         'business_key' => (string)$businessKey,
    //                         'location_id' => $destinationLocationId, // ULID string
    //                         'product_id' => $productId, // ULID string
    //                         'category_id' => $product->category_id, // Keep as is (might be integer or ULID)
    //                         'supplier_id' => $product->supplier_id, // Keep as is
    //                         'price' => (float)($product->price ?? 0),
    //                         'cost_price' => $product->cost_price ? (float)$product->cost_price : null,
    //                         'sale_price' => $product->sale_price ? (float)$product->sale_price : null,
    //                         'stock_quantity' => (int)$quantity,
    //                         'low_stock_threshold' => $lowStockThreshold,
    //                     ];

    //                     Log::info('Creating LocationProductList with data', [
    //                         'data' => $locationProductData,
    //                         'owner_id_type' => gettype($ownerId),
    //                         'location_id_type' => gettype($destinationLocationId),
    //                         'product_id_type' => gettype($productId),
    //                     ]);

    //                     $locationProduct = LocationProductList::create($locationProductData);

    //                     Log::info('LocationProductList created', [
    //                         'id' => $locationProduct->id
    //                     ]);

    //                     // Create item history record
    //                     try {
    //                         $historyData = [
    //                             'product_id' => $productId, // ULID string
    //                             'owner_id' => $ownerId, // ULID string - DON'T cast to int
    //                             'business_key' => $businessKey,
    //                             'location_id' => $destinationLocationId, // ULID string
    //                             'from_branch_id' => 1,
    //                             'to_branch_id' => 1,
    //                             'type' => $quantity > 0 ? 'Newly Added' : 'linked',
    //                             'quantity' => $quantity,
    //                             'cost' => $product->cost_price,
    //                             'price' => $product->price,
    //                             'source_id' => $ownerId, // ULID string
    //                             'source_type' => 'admin',
    //                             'note' => $notes ?? "Newly Added From Product Catalog / Head Office",
    //                             'transaction_date' => now(),
    //                         ];

    //                         Log::info('Creating ItemHistory', $historyData);

    //                         ItemHistory::create($historyData);

    //                         Log::info('ItemHistory created');
    //                     } catch (\Exception $e) {
    //                         Log::error('Failed to create ItemHistory', [
    //                             'product_id' => $productId,
    //                             'error' => $e->getMessage(),
    //                             'trace' => $e->getTraceAsString()
    //                         ]);
    //                         // Don't throw, continue processing
    //                     }
    //                 }

    //                 $processedItems[] = [
    //                     'product_id' => $productId,
    //                     'product_name' => $product->name,
    //                     'quantity' => $quantity,
    //                     'new_stock' => $locationProduct->stock_quantity,
    //                 ];

    //                 if ($quantity > 0) {
    //                     $totalItems += $quantity;
    //                 }

    //                 Log::info('Item processed successfully', [
    //                     'product_id' => $productId,
    //                     'processed_count' => count($processedItems)
    //                 ]);
    //             } catch (\Exception $e) {
    //                 Log::error('Error processing item', [
    //                     'item_index' => $index,
    //                     'item_data' => $item,
    //                     'error' => $e->getMessage(),
    //                     'file' => $e->getFile(),
    //                     'line' => $e->getLine(),
    //                     'trace' => $e->getTraceAsString()
    //                 ]);
    //                 $errors[] = "Error processing product ID {$item['product_id']}: {$e->getMessage()}";
    //                 continue;
    //             }
    //         }

    //         // Check if any items were processed
    //         if (empty($processedItems)) {
    //             Log::error('No items were processed', [
    //                 'errors' => $errors,
    //                 'total_items_in_request' => count($items)
    //             ]);

    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'No items could be processed',
    //                 'errors' => $errors
    //             ], 422);
    //         }

    //         DB::commit();
    //         Log::info('Transaction committed', [
    //             'processed_items' => count($processedItems),
    //             'total_units' => $totalItems,
    //             'errors_count' => count($errors)
    //         ]);

    //         $locationName = $destinationLocation->location_name ?? $destinationLocation->name ?? 'Unknown Location';

    //         $message = $totalItems > 0
    //             ? "Successfully added {$totalItems} units to {$locationName}"
    //             : count($items) . " products added to {$locationName} (no quantities specified)";

    //         if (!empty($errors)) {
    //             $message .= ". Some items had errors: " . implode('; ', $errors);
    //             Log::warning('Some items had errors', ['errors' => $errors]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => $message,
    //             'warnings' => !empty($errors) ? $errors : null,
    //             'data' => [
    //                 'destination' => [
    //                     'id' => $destinationLocation->id,
    //                     'name' => $locationName,
    //                 ],
    //                 'items_processed' => count($processedItems),
    //                 'total_units_added' => $totalItems,
    //                 'items' => $processedItems,
    //             ]
    //         ], 200);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         Log::error('Validation exception', [
    //             'errors' => $e->errors(),
    //             'request_data' => $request->all(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine()
    //         ]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Distribution failed', [
    //             'message' => $e->getMessage(),
    //             'code' => $e->getCode(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString(),
    //             'previous_exception' => $e->getPrevious() ? [
    //                 'message' => $e->getPrevious()->getMessage(),
    //                 'file' => $e->getPrevious()->getFile(),
    //                 'line' => $e->getPrevious()->getLine(),
    //             ] : null,
    //             'request_data' => $request->all(),
    //             'user_id' => Auth::id() ?? 'not authenticated',
    //             'user_id_type' => gettype(Auth::id()),
    //             'business_key' => $businessKey ?? 'not set',
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to distribute products',
    //             'error' => $e->getMessage(),
    //             'debug_info' => config('app.debug') ? [
    //                 'file' => $e->getFile(),
    //                 'line' => $e->getLine(),
    //             ] : null
    //         ], 500);
    //     }
    // }




    /**
     * Store new product
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 401);
        }

        //Manual validation for API-friendly errors
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:100',
                'sku' => 'nullable|string|max:50',
                'description' => 'nullable|string|max:450',

                'category_id' => 'required|string|exists:product_categories,id',
                'supplier_id' => 'required|string|exists:vendors,id',

                'products_measurements' => 'nullable|string|max:50',

                'price' => 'nullable|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',

                'stock_quantity' => 'nullable|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0|lte:stock_quantity',

                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_start_date' => 'nullable|date',
                'discount_end_date' => 'nullable|date|after:discount_start_date',

                'manufactured_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:manufactured_at',

                'weight' => 'nullable|numeric|min:0',
                'length' => 'nullable|numeric|min:0',
                'width'  => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',

                'image' => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/jpg,image/webp|max:2048',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'is_on_sale' => 'boolean',
            ],
            [
                'supplier_id.required' => 'The vendor name field is required.',
                'supplier_id.exists'   => 'The selected vendor is invalid.',
                'low_stock_threshold.lte' => 'Low stock threshold cannot be greater than available stock.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        try {

            // Handle profile picture
            $logoPath = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $file->storeAs('products_pictures', $filename, 'public');
                $logoPath = 'products_pictures/' . $filename;
            }
            $product = new Product_list();

            $product->owner_id = $user->id;
            $product->business_key = $user->active_business_key;
            $product->name = $validated['name'];
            $product->sku = $validated['sku'] ?? null;
            $product->description = $validated['description'] ?? null;

            $product->image =  $logoPath;

            // truncate slug to avoid DB length issues
            $nameSlug = Str::slug($validated['name']);
            $product->slug = strlen($nameSlug) > 240
                ? substr($nameSlug, 0, 240) . '-' . Str::random(6)
                : $nameSlug . '-' . Str::random(6);

            $product->category_id = $validated['category_id'];
            $product->supplier_id = $validated['supplier_id'];
            $product->product_measurements = $validated['products_measurements'];

            $product->price = $validated['price'] ?? 0;
            $product->cost_price = $validated['cost_price'] ?? 0;
            $product->sale_price = $validated['sale_price'] ?? null;

            $product->stock_quantity = $validated['stock_quantity'] ?? 0;
            $product->low_stock_threshold = $validated['low_stock_threshold'] ?? 0;

            $product->discount_percentage = $validated['discount_percentage'] ?? null;
            $product->discount_start_date = $validated['discount_start_date'] ?? null;
            $product->discount_end_date = $validated['discount_end_date'] ?? null;

            $product->manufactured_at = $validated['manufactured_at'] ?? null;
            $product->expires_at = $validated['expires_at'] ?? null;

            $product->weight = $validated['weight'] ?? null;
            $product->length = $validated['length'] ?? null;
            $product->width = $validated['width'] ?? null;
            $product->height = $validated['height'] ?? null;

            $product->is_active = $validated['is_active'] ?? true;
            $product->is_featured = $validated['is_featured'] ?? false;
            $product->is_on_sale = $validated['is_on_sale'] ?? false;
            $product->is_out_of_stock = false;

            //this is product api
            $product->image = $logoPath;
            $product->additional_info = null;
            $product->save();
            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage(), ['request' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show single product
     */
    // public function show($id)
    // {
    //     $id = Crypt::decrypt($id);

    //     $product = LocationProductList::where('id', $id)
    //         ->where('business_key', Auth::user()->active_business_key)
    //         ->firstOrFail();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $product
    //     ]);
    // }



    public function product_location_single($id, $locid)
    {
        $user = Auth::user();

        Log::info('Product location single request started', [
            'encrypted_product_id' => $id,
            'encrypted_product_id_length' => strlen($id),
            'encrypted_location_id' => $locid,
            'encrypted_location_id_length' => strlen($locid),
            'user_id' => Auth::id()
        ]);

        if (!$user) {
            Log::warning('Unauthenticated access attempt to product_location_single');
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (empty($user->active_business_key)) {
            Log::warning('No active business selected', [
                'user_id' => $user->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 400);
        }

        if (!$user->hasPermission('product_read')) {
            Log::warning('Permission denied for product_read', [
                'user_id' => $user->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this product.'
            ], 403);
        }

        try {
            Log::info('Attempting to decrypt IDs', [
                'encrypted_product_id' => $id,
                'encrypted_location_id' => $locid
            ]);

            $decryptedId = ($id);
            $decryptedLoc = ($locid);

            Log::info('IDs decrypted successfully', [
                'decrypted_product_id' => $decryptedId,
                'decrypted_location_id' => $decryptedLoc,
                'decrypted_product_id_type' => gettype($decryptedId),
                'decrypted_location_id_type' => gettype($decryptedLoc)
            ]);

            // Check if the LocationProductList model/table exists
            Log::info('Checking LocationProductList model', [
                'model_class' => LocationProductList::class,
                'table_name' => (new LocationProductList())->getTable()
            ]);

            // Build query step by step for better debugging
            $query = LocationProductList::query();

            Log::info('Base query built');

            // Add relationships
            $query->with([
                'product:id,name,slug,description,sku,dimensions,image,barcode,is_active',
                'category:id,name',
                'location:id,location_name,address,city,state,country,postal_code,head_office',
            ]);

            Log::info('Relationships added to query');

            // Add where conditions
            $query->where('business_key', $user->active_business_key);

            Log::info('Business key filter added', [
                'business_key' => $user->active_business_key
            ]);

            $query->where('product_id', $decryptedId);

            Log::info('Product ID filter added', [
                'product_id' => $decryptedId
            ]);

            $query->where('location_id', $decryptedLoc);

            Log::info('Location ID filter added', [
                'location_id' => $decryptedLoc
            ]);

            // Log the SQL query for debugging
            Log::info('SQL Query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            // Execute the query
            $productLocation = $query->first();

            Log::info('Query executed', [
                'found' => $productLocation ? 'yes' : 'no',
                'product_location_id' => $productLocation?->id
            ]);

            // Check if product exists
            if (!$productLocation) {
                // Let's check if there are ANY products at this location
                $anyProducts = LocationProductList::where('location_id', $decryptedLoc)
                    ->where('business_key', $user->active_business_key)
                    ->count();

                Log::warning('Product not found at specified location', [
                    'decrypted_product_id' => $decryptedId,
                    'decrypted_location_id' => $decryptedLoc,
                    'business_key' => $user->active_business_key,
                    'total_products_at_location' => $anyProducts
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found at this location'
                ], 404);
            }

            // Encrypt the ID for frontend use
            $productLocation->encrypted_id = ($productLocation->id);

            Log::info('Product location found and response prepared', [
                'product_location_id' => $productLocation->id,
                'product_name' => $productLocation->product->name ?? 'N/A',
                'stock_quantity' => $productLocation->stock_quantity
            ]);

            return response()->json([
                'success' => true,
                'data'    => $productLocation,
                'message' => 'Product location retrieved successfully'
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Failed to decrypt IDs', [
                'encrypted_product_id' => $id,
                'encrypted_location_id' => $locid,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid encrypted ID'
            ], 400);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in product_location_single', [
                'encrypted_product_id' => $id,
                'encrypted_location_id' => $locid,
                'decrypted_product_id' => $decryptedId ?? null,
                'decrypted_location_id' => $decryptedLoc ?? null,
                'error_message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'error_code' => $e->getCode()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database error while fetching product details: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('General error in product_location_single', [
                'encrypted_product_id' => $id,
                'encrypted_location_id' => $locid,
                'decrypted_product_id' => $decryptedId ?? 'not_decrypted',
                'decrypted_location_id' => $decryptedLoc ?? 'not_decrypted',
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching product details: ' . $e->getMessage()
            ], 500);
        }
    }



    public function transferstock(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => 'required|string|exists:business_locations,id',
            'to_location_id' => [
                'required',
                'string',
                'exists:business_locations,id',
                'different:from_location_id',
            ],
            'transfer_date' => 'required|date|after_or_equal:today',
            'expected_delivery_date' => 'nullable|date|after_or_equal:transfer_date',
            'notes' => 'nullable|string|max:1000',
            'reference_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('stock_transfers', 'reference_number'),
            ],
            'product_id' => [
                'required',
                'string',
                'exists:product_lists,id',
            ],
            'stock_quantity' => 'required|integer|min:1',
            'stock_quantity_before' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'business_key' => [
                'required',
                'string',
                'exists:business_lists,business_key',
            ],
            'postby' => 'nullable|string|max:255',
        ]);

        // Validate that transfer quantity doesn't exceed available stock
        if ($validated['stock_quantity'] > $validated['stock_quantity_before']) {
            return response()->json([
                'message' => 'Transfer quantity cannot exceed available stock',
                'errors' => [
                    'stock_quantity' => [
                        "Cannot transfer {$validated['stock_quantity']} units. Only {$validated['stock_quantity_before']} available."
                    ]
                ]
            ], 422);
        }

        // Verify total calculation
        $calculatedTotal = $validated['stock_quantity'] * $validated['unit_cost'];
        if (abs($calculatedTotal - $validated['total']) > 0.01) {
            return response()->json([
                'message' => 'Total amount mismatch',
                'errors' => [
                    'total' => ['The calculated total does not match the provided total.']
                ]
            ], 422);
        }

        // Verify product exists at source location with sufficient stock
        $sourceProduct = LocationProductList::where('product_id', $validated['product_id'])
            ->where('location_id', $validated['from_location_id'])
            ->where('business_key', $validated['business_key'])
            ->first();

        if (!$sourceProduct) {
            return response()->json([
                'message' => 'Product not found at source location',
            ], 404);
        }

        if ($sourceProduct->stock_quantity < $validated['stock_quantity']) {
            return response()->json([
                'message' => 'Insufficient stock at source location',
                'errors' => [
                    'stock_quantity' => [
                        "Only {$sourceProduct->stock_quantity} units available at source location."
                    ]
                ]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create the stock transfer record
            $stockTransfer = StockTransfer::create($validated);

            // Deduct stock from source location
            $sourceProduct->decrement('stock_quantity', $validated['stock_quantity']);

            // Update or create product record at destination location
            $destinationProduct = LocationProductList::where('product_id', $validated['product_id'])
                ->where('location_id', $validated['to_location_id'])
                ->where('business_key', $validated['business_key'])
                ->first();

            if ($destinationProduct) {
                // Product already exists at destination — just increment
                $destinationProduct->increment('stock_quantity', $validated['stock_quantity']);
            } else {
                // Product doesn't exist at destination — create it, mirroring source fields
                LocationProductList::create([
                    'product_id'           => $validated['product_id'],
                    'location_id'          => $validated['to_location_id'],
                    'business_key'         => $validated['business_key'],
                    'stock_quantity'       => $validated['stock_quantity'],
                    'owner_id'             => $sourceProduct->owner_id,
                    'category_id'          => $sourceProduct->category_id,
                    'supplier_id'          => $sourceProduct->supplier_id,
                    'price'                => $sourceProduct->price,
                    'cost_price'           => $sourceProduct->cost_price,
                    'sale_price'           => $sourceProduct->sale_price,
                    'low_stock_threshold'  => $sourceProduct->low_stock_threshold,
                    'manufactured_at'      => $sourceProduct->manufactured_at,
                    'expires_at'           => $sourceProduct->expires_at,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Stock transfer created successfully',
                'data' => $stockTransfer->load(['fromLocation', 'toLocation', 'product']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Stock transfer creation failed: ' . $e->getMessage(), [
                'payload' => $validated,
                'error' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to create stock transfer',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }




    public function transfer_stocks(Request $request)
    {
        // 1. Validate raw input
        $validator = Validator::make($request->all(), [
            'sourceLocation'       => 'required|string',
            'destinationLocation'  => 'required|string',
            'transferDate'         => 'required|date',
            'expectedDelivery'     => 'nullable|date|after_or_equal:transferDate',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:product_location,product_id',
            'items.*.stock_quantity' => 'required|integer|min:1',
            'items.*.unit_cost'    => 'required|numeric|min:0',
            'notes'                => 'nullable|string|max:5000',
            'reference_number'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Decrypt location IDs (Laravel's built-in encryption)
        try {
            $sourceLocationId = Crypt::decryptString($request->sourceLocation);
            $destinationLocationId = Crypt::decryptString($request->destinationLocation);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid location identifiers.'], 400);
        }

        // 3. Verify locations exist and are different
        $sourceLocation = Location::find($sourceLocationId);
        $destinationLocation = Location::find($destinationLocationId);

        if (! $sourceLocation || ! $destinationLocation) {
            return response()->json(['message' => 'One or both locations not found.'], 404);
        }

        if ($sourceLocationId === $destinationLocationId) {
            return response()->json(['message' => 'Source and destination locations must be different.'], 422);
        }

        // 4. Check stock availability & prepare items
        $itemsData = [];
        foreach ($request->items as $item) {
            $productLocation = ProductLocation::where('location_id', $sourceLocationId)
                ->where('product_id', $item['product_id'])
                ->first();

            if (! $productLocation) {
                return response()->json([
                    'message' => "Product ID {$item['product_id']} is not available at the source location."
                ], 422);
            }

            if ($productLocation->stock_quantity < $item['stock_quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product ID {$item['product_id']}. " .
                        "Available: {$productLocation->stock_quantity}, requested: {$item['stock_quantity']}"
                ], 422);
            }

            $itemsData[] = [
                'product_id'      => $item['product_id'],
                'stock_quantity'  => $item['stock_quantity'],
                'unit_cost'       => $item['unit_cost'],
                'total'           => $item['stock_quantity'] * $item['unit_cost'],
            ];
        }

        // 5. Create the transfer inside a database transaction
        try {
            DB::beginTransaction();

            $transfer = StockTransfer::create([
                'from_location_id'        => $sourceLocationId,
                'to_location_id'          => $destinationLocationId,
                'transfer_date'           => $request->transferDate,
                'expected_delivery_date'  => $request->expectedDelivery,
                'notes'                   => $request->notes,
                'reference_number'        => $request->reference_number,
                'status'                  => 'pending', // or 'draft'
                'created_by'              => auth()->id(),
            ]);

            // Attach items
            foreach ($itemsData as $item) {
                $transfer->items()->create($item);
            }

            DB::commit();

            // Load items relationship for response
            $transfer->load('items');

            return response()->json([
                'message' => 'Stock transfer created successfully.',
                'data'    => $transfer,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock transfer creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the transfer. Please try again.',
            ], 500);
        }
    }












    public function product_history($id, $locid)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (empty($user->active_business_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 400);
        }

        if (!$user->hasPermission('product_read')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this product.'
            ], 403);
        }

        try {
            $decryptedId = ($id);
            $decryptedloc = ($locid);

            // Query parameters for filtering and pagination
            $perPage = request()->input('per_page', 10);
            $startDate = request()->input('start_date');
            $endDate = request()->input('end_date');

            // Build query
            $query = ItemHistory::with([
                'product:id,name,slug,description,sku,dimensions,image',
                'location_info:id,location_name'
            ])
                ->where('business_key', $user->active_business_key)
                ->where('product_id', $decryptedId)->where('location_id', $decryptedloc);

            // Apply date range filter
            if ($startDate) {
                $query->whereDate('transaction_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('transaction_date', '<=', $endDate);
            }

            // Paginate (ordered by latest transaction)
            $products = $query->orderBy('transaction_date', 'desc')
                ->paginate($perPage);

            // Encrypt IDs for each item
            $products->getCollection()->transform(function ($item) {
                $item->encrypted_id = ($item->id);
                return $item;
            });

            // Fetch location name from the first item (if any)
            $firstItem = $products->first();

            return response()->json([
                'success'       => true,
                'data'          => $products->items(),
                'current_page'  => $products->currentPage(),
                'last_page'     => $products->lastPage(),
                'per_page'      => $products->perPage(),
                'total'         => $products->total(),
                'product_name'  => $firstItem?->product?->name,
                'location_name' => $firstItem?->location_info?->location_name,
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            Log::error('Failed to decrypt product ID', [
                'encrypted_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid encrypted ID',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Product history error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }






    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (empty($user->active_business_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 400);
        }

        if (!$user->hasPermission('product_read')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this product.'
            ], 403);
        }

        // Decode + decrypt ONLY (this is the only risky part)
        // try {
        //     $id = (str) $id;
        // } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid product ID',
        //     ], 400);
        // }

        // Query without try/catch
        $product = Product_list::with(['category:id,name'])
            ->where('business_key', $user->active_business_key)
            ->where('id', $id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $product->encrypted_id = Crypt::encryptString($product->id);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }


    public function update(Request $request, $id)
    {

        $user = Auth::user();


        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (empty($user->active_business_key)) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected'
            ], 400);
        }

        if (!$user->hasPermission('product_update')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this product.'
            ], 403);
        }

        // Use decryptString to match encryptString from index method
        // $id = Crypt::decryptString($id);

        $product = Product_list::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'sku' => [
                'sometimes',
                Rule::unique('product_lists')
                    ->ignore($product->id)
                    ->where(
                        fn($q) =>
                        $q->where('business_key', $user->active_business_key)
                    ),
            ],
            'description' => 'nullable|string|max:450',

            'category_id' => 'required|string|exists:product_categories,id|max:50',
            'supplier_id' => 'required|string|exists:vendors,id|max:50',

            'products_measurements' => 'nullable|string|max:50',

            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',

            'stock_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0|lte:stock_quantity',

            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after:discount_start_date',

            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:manufactured_at',

            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width'  => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',

            'image' => 'nullable|mimetypes:image/avif,image/jpeg,image/png,image/jpg,image/webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_on_sale' => 'boolean',

        ]);

        // Handle Image Upload
        if ($request->hasFile('image')) {

            // Delete old image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Store new image
            $imagePath = $request->file('image')->store('products', 'public');

            // Add image path to validated array
            $validated['image'] = $imagePath;
        }

        // Handle slug update if name exists
        if ($request->filled('name')) {
            $validated['slug'] = Str::slug($request->name);
        }

        // Update product
        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }


    public function update_product(Request $request, $id)
    {
        $user = Auth::user();

        try {

            $decryptedId = ($id);

            $product = LocationProductList::where('id', $decryptedId)
                ->where('business_key', $user->active_business_key)
                ->firstOrFail();

            $validated = $request->validate([
                // 'product_name' => 'sometimes|string|max:255',
                'sku' => 'nullable|string|max:100',
                // 'barcode' => 'nullable|string|max:100',
                'stock_quantity' => 'nullable|numeric|min:0',
                'low_stock_threshold' => 'nullable|numeric|min:0',
                'manufactured_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after:manufactured_at'
            ]);

            $product->update($validated);



            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {

            Log::error('Product update failed', [
                'error' => $e->getMessage(),
                'encrypted_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product update failed'
            ], 500);
        }
    }

    // public function product_location_update(Request $request, $id)
    // {
    //     $user = Auth::user();

    //     Log::info('Product location update request received', [
    //         'encrypted_id' => $id,
    //         'user_id' => $user->id ?? null,
    //         'business_key' => $user->active_business_key ?? null,
    //         'payload' => $request->all()
    //     ]);

    // }


    /**
     * Delete product
     */
    public function destroy($id)
    {
        $product = Product_list::where('id', $id)
            ->where('business_key', Auth::user()->active_business_key)
            ->firstOrFail();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
