<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product_list;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;




class ProductController extends Controller
{
    /**
     * List products (per business)
     */
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
            ->get();

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
                'name' => 'required|string|max:255',
                'sku' => 'nullable|string|max:50',
                'description' => 'nullable|string',

                'category_id' => 'required|exists:product_categories,id',
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

               'image'=>'nullable|mimetypes:image/avif,image/jpeg,image/png,image/jpg,image/webp|max:2048',
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
            $product->image=$logoPath;
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

    /**
     * Update product
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $product = Product_list::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => [
                'sometimes',
                Rule::unique('product_lists')
                    ->ignore($product->id)
                    ->where(
                        fn($q) =>
                        $q->where('business_key', $user->active_business_key)
                    )
            ],
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
        ]);

        $product->update(array_merge(
            $request->all(),
            ['slug' => $request->name ? Str::slug($request->name) : $product->slug]
        ));

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
