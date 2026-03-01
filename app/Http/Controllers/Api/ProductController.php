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
use Illuminate\Support\Facades\Log; // <-- ADD THIS LINE
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function locationproducts($id)
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

        // Permission guard
        if (!$user->hasPermission('locations_read')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature Unavailable.'
            ], 403);
        }

        // Decrypt location id safely
        try {
            $locationId = Crypt::decrypt($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid location id'
            ], 400);
        }
        $location = Business_locations::where('id', $locationId)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Location not found'
            ], 404);
        }

        //Ensure location belongs to user's business
        $products = LocationProductList::with([
            'product:id,name,slug,description,sku,dimensions,discount_percentage,discount_start_date,discount_end_date,manufactured_at,expires_at,weight,length,width,height,is_active,is_featured,is_on_sale,is_out_of_stock,image,additional_info,barcode',
            'category:id,name'
        ])
            ->where('business_key', $user->active_business_key)
            ->where('location_id', $locationId)
            ->get()
            ->map(function ($item) use ($location) {
                $item->encrypted_id = Crypt::encryptString($item->id);

                //correct location name
                $item->location_name = $location->location_name;

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $products,
            'location_name' =>$location->location_name,
            'count' => $products->count()
        ]);
    }



    public function distributeProducts(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Distribution request received', [
                'data' => $request->all(),
                'user_id' => Auth::id(),
                'business_key' => Auth::user()->active_business_key ?? 'not set'
            ]);

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

            // FIXED: Use correct model name (CamelCase)
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

                // FIXED: Use correct model name (CamelCase)
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
                'data' => [
                    'destination' => [
                        'id' => $destinationLocation->id,
                        'name' => $destinationLocation->name,
                    ],
                    'items_processed' => count($items),
                    'total_units_added' => $totalItems,
                    'items' => $processedItems,
                ]
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




    public function index()
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

        $products = Product_list::where('business_key', $user->active_business_key)
            ->get()
            ->map(function ($product) {
                $product->encrypted_id = Crypt::encryptString($product->id);
                return $product;
            });

        return response()->json([
            'success' => true,
            'data' => $products,
            'count' => $products->count()
        ]);
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

        // ✅ Manual validation for API-friendly errors
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
            \Log::error('Product creation failed: ' . $e->getMessage(), ['request' => $request->all()]);
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
    public function show($id)
    {
        $product = Product_list::where('id', $id)
            ->where('business_key', Auth::user()->active_business_key)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();

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
