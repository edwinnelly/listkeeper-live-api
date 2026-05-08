<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business_locations;
use App\Models\ItemHistory;
use App\Models\LocationProductList;
use App\Models\Product_list;
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
            $product->encrypted_id = Crypt::encryptString($product->id);
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
    //     if (!$user) {
    //         return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
    //     }

    //     if (empty($user->active_business_key)) {
    //         return response()->json(['success' => false, 'message' => 'No active business selected'], 400);
    //     }

    //     if (!$user->hasPermission('locations_read')) {
    //         return response()->json(['success' => false, 'message' => 'Feature Unavailable.'], 403);
    //     }

    //     $request->validate([
    //         'price_min' => 'nullable|numeric|min:0',
    //         'price_max' => 'nullable|numeric|min:0',
    //         'per_page'  => 'nullable|integer|min:1|max:100',
    //     ]);

    //     try {
    //         $locationId = Crypt::decrypt($id);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Invalid location id'], 400);
    //     }

    //     $location = Business_locations::where('id', $locationId)
    //         ->where('business_key', $user->active_business_key)
    //         ->first();

    //     if (!$location) {
    //         return response()->json(['success' => false, 'message' => 'Location not found'], 404);
    //     }

    //     $perPage = (int) $request->input('per_page', 15);
    //     $perPage = min(max($perPage, 1), 100);

    //     try {
    //         $query = LocationProductList::with([
    //             'product:id,name,slug,description,sku,dimensions,discount_percentage,discount_start_date,discount_end_date,manufactured_at,expires_at,weight,length,width,height,is_active,is_featured,is_on_sale,is_out_of_stock,image,additional_info,barcode',
    //             'category:id,name'
    //         ])
    //             ->where('business_key', $user->active_business_key)
    //             ->where('location_id', $locationId);

    //         // ---- Search (case‑insensitive) ----
    //         if ($search = $request->input('search')) {
    //             $query->whereHas('product', function ($q) use ($search) {
    //                 $q->where('name', 'ilike', "%{$search}%")
    //                     ->orWhere('sku', 'ilike', "%{$search}%")
    //                     ->orWhere('description', 'ilike', "%{$search}%");
    //             });
    //         }

    //         // ---- Status filter (uses product's is_active) ----
    //         if (($status = $request->input('status')) && in_array($status, ['active', 'inactive'])) {
    //             $query->whereHas('product', function ($q) use ($status) {
    //                 $q->where('is_active', $status === 'active');
    //             });
    //         }

    //         // ---- Category filter ----
    //         if ($categoryId = $request->input('category')) {
    //             $query->where('category_id', $categoryId);
    //         }

    //         // ---- Stock level filter (combines location & product) ----
    //         $stock = $request->input('stock');
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
    //             $query->where('price', '>=', (float) $priceMin);
    //         }
    //         if ($priceMax = $request->input('price_max')) {
    //             $query->where('price', '<=', (float) $priceMax);
    //         }

    //         // ---- Sorting ----
    //         $sortBy = $request->input('sort_by', 'created');
    //         $sortOrder = strtolower($request->input('sort_order', 'desc'));
    //         $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';

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

    //         $paginator = $query->paginate($perPage);

    //         $paginator->getCollection()->transform(function ($item) use ($location) {
    //             $item->encrypted_id = Crypt::encrypt($item->id);
    //             $item->encrypted_pid = Crypt::encrypt($item->product_id);
    //             $item->location_name = $location->location_name;

    //             // Copy product‑level fields that the frontend expects directly
    //             if ($item->product) {
    //                 $item->is_active       = $item->product->is_active;
    //                 $item->is_out_of_stock = $item->product->is_out_of_stock;
    //                 $item->is_featured     = $item->product->is_featured;
    //                 $item->is_on_sale      = $item->product->is_on_sale;
    //             } else {
    //                 $item->is_active       = false;
    //                 $item->is_out_of_stock = false;
    //                 $item->is_featured     = false;
    //                 $item->is_on_sale      = false;
    //             }

    //             return $item;
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'data' => $paginator->items(),
    //             'location_name' => $location->location_name,
    //             'pagination' => [
    //                 'current_page' => $paginator->currentPage(),
    //                 'last_page' => $paginator->lastPage(),
    //                 'per_page' => $paginator->perPage(),
    //                 'total' => $paginator->total(),
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('LocationProducts Error', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'params' => $request->all(),
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
        'category'  => 'nullable|integer|exists:product_categories,id',
    ]);

    try {
        $locationId = Crypt::decrypt($id);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Invalid location id'], 400);
    }

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

        // ---- Category filter (via product) ----
        if ($categoryId = $request->input('category')) {
            Log::info('Category filter applied (via product)', ['category_id' => $categoryId]);
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

        $paginator = $query->paginate($perPage);

        $paginator->getCollection()->transform(function ($item) use ($location) {
            $item->encrypted_id = Crypt::encrypt($item->id);
            $item->encrypted_pid = Crypt::encrypt($item->product_id);
            $item->location_name = $location->location_name;

            if ($item->product) {
                $item->is_active       = $item->product->is_active;
                $item->is_out_of_stock = $item->product->is_out_of_stock;
                $item->is_featured     = $item->product->is_featured;
                $item->is_on_sale      = $item->product->is_on_sale;
            } else {
                $item->is_active       = false;
                $item->is_out_of_stock = false;
                $item->is_featured     = false;
                $item->is_on_sale      = false;
            }

            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'location_name' => $location->location_name,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('LocationProducts Error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'params' => $request->all(),
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
            $validated = $request->validate([
                'destination_location_id' => [
                    'required',
                    'exists:business_locations,id',
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
                    'exists:product_lists,id',
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


            if (!$businessKey) {
                throw new \Exception('Active business key not found for user');
            }

            $destinationLocationId = $request->destination_location_id;
            $items = $request->items;


            $destinationLocation = Business_locations::where('id', $destinationLocationId)
                ->where('business_key', $businessKey)
                ->first();

            if (!$destinationLocation) {
                throw new \Exception("Location not found or doesn't belong to business");
            }

            $processedItems = [];
            $totalItems = 0;

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = (int)$item['quantity']; // Cast to integer


                $product = Product_list::where('id', $productId)
                    ->where('business_key', $businessKey)
                    ->first();

                if (!$product) {
                    throw new \Exception("Product ID  not found or doesn't belong to business");
                }

                // Check if product already exists at this location
                $existingProduct = LocationProductList::where('location_id', $destinationLocationId)
                    ->where('product_id', $productId)
                    ->where('business_key', $businessKey)
                    ->first();

                if ($existingProduct) {
                    // Update existing record - add quantity to stock
                    $newStock = (int)($existingProduct->stock_quantity + $quantity);
                    $existingProduct->update([
                        'stock_quantity' => $newStock,
                    ]);

                    $locationProduct = $existingProduct;
                } else {
                    // FIXED: Cast low_stock_threshold to integer properly
                    $lowStockThreshold = 0; // Default
                    if ($product->low_stock_threshold !== null) {
                        // If it's a string like "22.00", convert to integer
                        if (is_string($product->low_stock_threshold)) {
                            $lowStockThreshold = (int)round((float)$product->low_stock_threshold);
                        } else {
                            $lowStockThreshold = (int)$product->low_stock_threshold;
                        }
                    }

                    // Create new record with all required fields and proper casting
                    $locationProduct = LocationProductList::create([
                        'owner_id' => (int)$ownerId,
                        'business_key' => (string)$businessKey,
                        'location_id' => (int)$destinationLocationId,
                        'product_id' => (int)$productId,
                        'category_id' => $product->category_id ? (int)$product->category_id : null,
                        'supplier_id' => $product->supplier_id ? (int)$product->supplier_id : null,
                        'price' => (float)($product->price ?? 0),
                        'cost_price' => $product->cost_price ? (float)$product->cost_price : null,
                        'sale_price' => $product->sale_price ? (float)$product->sale_price : null,
                        'stock_quantity' => (int)$quantity,
                        'low_stock_threshold' => 0, // Now it's definitely an integer
                    ]);

                    // Create item history record
                    ItemHistory::create([
                        'product_id' => $productId,
                        'owner_id' => $ownerId,
                        'business_key' => $businessKey,
                        'location_id' => $destinationLocationId,
                        'from_branch_id' => 1,
                        'to_branch_id' => 1,
                        'type' => $quantity > 0 ? 'addition' : 'linked',
                        'quantity' => $quantity,
                        'cost' => $product->cost_price,
                        'price' => $product->price,
                        'source_id' => $ownerId,
                        'source_type' => 'admin', // Full namespace as string
                        'note' => $notes ?? "Newly Added From Head Office",
                        'transaction_date' => now(),
                    ]);
                }

                $processedItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'new_stock' => $locationProduct->stock_quantity,
                ];

                if ($quantity > 0) {
                    $totalItems += $quantity;
                }
            }

            DB::commit();

            $message = $totalItems > 0
                ? "Successfully added {$totalItems} units to {$destinationLocation->name}"
                : count($items) . " products added to {$destinationLocation->name} (no quantities specified)";

            return response()->json([
                'success' => true,
                'message' => $message,
                // 'data' => [
                //     'destination' => [
                //         'id' => $destinationLocation->id,
                //         'name' => $destinationLocation->name,
                //     ],
                //     'items_processed' => count($items),
                //     'total_units_added' => $totalItems,
                //     'items' => $processedItems,
                // ]
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

            // Log the full error details
            Log::error('Distribution failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to distribute products',
                'error' => $e->getMessage()
            ], 500);
        }
    }





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

                'category_id' => 'required|integer|exists:product_categories,id',
                'supplier_id' => 'required|integer|exists:vendors,id',

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

    public function product_history($id)
    {
        $user = Auth::user();

        try {
            // Decrypt ID
            $decryptedId = Crypt::decrypt($id);

            // Fetch history records
            $products = ItemHistory::with([
                'product:id,name,slug,description,sku,dimensions,image',
                'location_info:id,location_name'
            ])
                ->where('business_key', $user->active_business_key)
                ->where('product_id', $decryptedId)
                ->get();

            // Encrypt IDs
            $products->transform(function ($item) {
                $item->encrypted_id = Crypt::encrypt($item->id);
                return $item;
            });

            // Get first record safely
            $firstItem = $products->first();

            return response()->json([
                'success' => true,
                'count' => $products->count(),
                'data' => $products,
                'product_name' => $firstItem?->product?->name,
                'location_name' => $firstItem?->location_info?->location_name,
            ]);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {

            Log::error('Failed to decrypt product ID', [
                'encrypted_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid encrypted ID',
            ], 400);
        } catch (\Exception $e) {



            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    // public function product_history($id)
    // {
    //     $user = Auth::user();
    //     try {
    //         // Decrypt ID
    //         $decryptedId = Crypt::decrypt($id);
    //         // Fetch all history records
    //         $products = ItemHistory::with([
    //             'product:id,name,slug,description,sku,dimensions,image'  
    //         ])
    //         ->where('business_key', $user->active_business_key)
    //         ->where('product_id', $decryptedId)
    //         ->get();

    //         // Add encrypted IDs
    //         $products->transform(function ($item) {
    //             $item->encrypted_id = Crypt::encrypt($item->id);
    //             return $item;
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'count' => $products->count(),
    //             'data' => $products,
    //             'product_name' => $products->first()->name ?? null,
    //         ]);

    //     } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {

    //         Log::error('Failed to decrypt product ID', [
    //             'encrypted_id' => $id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid encrypted ID',
    //         ], 400);

    //     } catch (\Exception $e) {

    //         Log::error('Unexpected error in product_history()', [
    //             'encrypted_id' => $id,
    //             'user_id' => $user->id,
    //             'error' => $e->getMessage(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong',
    //         ], 500);
    //     }
    // }



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
        try {
            $id = (int) Crypt::decryptString(urldecode($id));
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID',
            ], 400);
        }

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
        $id = Crypt::decryptString($id);

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

            'category_id' => 'required|integer|exists:product_categories,id',
            'supplier_id' => 'required|integer|exists:vendors,id',

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

            $decryptedId = Crypt::decrypt($id);

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

            Log::info('Product updated successfully', [
                'product_id' => $product->id,
                'updated_fields' => $validated
            ]);

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
