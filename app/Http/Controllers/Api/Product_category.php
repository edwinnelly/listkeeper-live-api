<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product_categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class Product_category extends Controller
{

    public function index()
    {
        $user = auth()->user();

        // Check if user is authenticated and has an active business
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Check permission for product categories
        if (!$user->hasPermission('category_read')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }

        // Cache key per business 
        $cacheKey = "product_categories_{$user->active_business_key}";

        // Cache for 60 
        $productCategories = Cache::remember($cacheKey, 60, function () use ($user) {
            return Product_categories::withCount('products')
                ->where('business_key', $user->active_business_key)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'owner_id' => $category->owner_id,
                        'business_key' => $category->business_key,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                        'is_active' => (bool)$category->is_active,
                        'created_at' => $category->created_at,
                        'updated_at' => $category->updated_at,
                        'product_count' => $category->products_count, // use eager-loaded count
                    ];
                });
        });

        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => 'Product categories retrieved successfully',
            'data' => [
                'product_categories' => $productCategories,
                'total' => $productCategories->count(),
                'active_count' => $productCategories->where('is_active', true)->count(),
            ]
        ]);
    }


    public function storeCategory(Request $request)
    {
        $loggedInUser = Auth::user();
        // Check if user is authenticated and has an active business
        if (!$loggedInUser || !$loggedInUser->active_business_key) {
            return response()->json([
                'error' => 'No active business selected.'
            ], 403);
        }

        // Check permission for product categories
        if (!$loggedInUser->hasPermission('category_create')) {
            return response()->json([
                'error' => 'Feature Unavailable.'
            ], 403);
        }
        // 1. Validate request
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('product_categories')
                    ->where('business_key', $loggedInUser->active_business_key),
            ],
            'description' => 'nullable|string|max:500',
            'is_active'   => 'required|boolean',
        ]);

        try {
            // 2. Create category
            $category = Product_categories::create([
                'owner_id'     => $loggedInUser->id,
                'business_key' => $loggedInUser->active_business_key,
                'name'         => $validated['name'],
                'slug'         => Str::slug($validated['name']),
                'description'  => $validated['description'] ?? null,
                'is_active'    => $validated['is_active'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data'    => $category,
            ], 201);
        } catch (\Throwable $e) {

            Log::error('Category creation failed', [
                'user_id' => $loggedInUser->id,
                'business_key' => $loggedInUser->active_business_key,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
            ], 500);
        }
    }



    public function updateCategory(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Auth & business check
        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        // 2. Permission check
        if (!$user->hasPermission('category_update')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        // 3. Fetch category (business-scoped)
        $category = Product_categories::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        // 4. Validate request
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('product_categories')
                    ->where('business_key', $user->active_business_key)
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string|max:500',
            'is_active'   => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            // 5. Regenerate slug only if name changes
            $slug = $category->slug;
            if ($validated['name'] !== $category->name) {
                $slug = Str::slug($validated['name']);

                $slugExists = Product_categories::where('business_key', $user->active_business_key)
                    ->where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists();

                if ($slugExists) {
                    $slug .= '-' . Str::random(4);
                }
            }

            // 6. Update category
            $category->update([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data'    => $category,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Category update failed', [
                'user_id'      => $user->id,
                'business_key' => $user->active_business_key,
                'category_id'  => $id,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update category.',
            ], 500);
        }
    }

    public function deleteCategory($id)
    {
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            return response()->json([
                'success' => false,
                'message' => 'No active business selected.'
            ], 403);
        }

        if (!$user->hasPermission('category_delete')) {
            return response()->json([
                'success' => false,
                'message' => 'Feature unavailable.'
            ], 403);
        }

        $category = Product_categories::where('id', $id)
            ->where('business_key', $user->active_business_key)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        try {
            DB::transaction(function () use ($category) {
                $category->delete(); // soft delete if enabled
            });

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Category delete failed', [
                'user_id'      => $user->id,
                'business_key' => $user->active_business_key,
                'category_id'  => $id,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category.'
            ], 500);
        }
    }
}
