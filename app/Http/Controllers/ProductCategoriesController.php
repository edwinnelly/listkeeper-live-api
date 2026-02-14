<?php

namespace App\Http\Controllers;

use App\Models\Product_categories;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ProductCategoriesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //fetch all the category
        $businessKey = auth()->user()->active_business_key;
        $ownerId = auth()->id();
        $fetch_all = Product_categories::where('business_key', $businessKey)->get();
        return view('products_category.products_category', compact('fetch_all'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $businessKey = auth()->user()->active_business_key;
        $ownerId = auth()->id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:50|unique:product_categories',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $category = new Product_categories();
            $category->name = $validated['name'];
            $category->slug = $validated['slug'];
            $category->description = $validated['description'] ?? null;
            $category->business_key = $businessKey;
            $category->owner_id = $ownerId;
            $category->save();

            DB::commit();

            return redirect()->route('products.category.index')
                ->with('success', 'Product category created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'An error occurred while saving the product category.']);
        }
    }



    public function delete_pro_category($id)
    {
        // Decrypt the business key
        $id = Crypt::decrypt($id);

        $fetchCateogry = Product_categories::where('id', $id)->firstOrFail();
        return view('products_category.product_category_deletes', compact('fetchCateogry'));
    }


    /**
     * Display the specified resource.
     */
    public function show(Product_categories $product_categories, $id)
    {
        $businessKey = auth()->user()->active_business_key;
        $ownerId = auth()->id();
        // Decrypt the business key
        $id = Crypt::decrypt($id);
        //get all the category
        $category = Product_categories::where('business_key', $businessKey)->where('id', $id)->first();
        // dd($category);
        return view('products_category.product_category_edit', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product_categories $product_categories)
    {
        //
    }

    public function test()
    {
        $category = Product_categories::with('products')->find(10);
        $products = $category->products;
        dd($products);
    }
    public function showProducts($id)
    {
        try {
            if (empty($id)) {
                return redirect()->route('categories.index')->with('error', 'Invalid category filter.');
            }

            $id = Crypt::decrypt($id);

            $category = Product_categories::with('products')->findOrFail($id);
            $products = $category->products;

            return view('products_category.products_under_category', compact('category', 'products'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid Category');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Decrypt the ID
            $id = Crypt::decrypt($id);

            // Fetch the category
            $category = Product_categories::findOrFail($id);

            // Validate input
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:product_categories,slug,' . $category->id,
                'description' => 'nullable|string',
            ]);

            // Update the category
            $category->update([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ]);

            DB::commit();

            return redirect()->route('products.category.index')
                ->with('success', 'Category updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update product category: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        try {
            // Decrypt the user ID
            $id = Crypt::decrypt($id);
            // Check if the category  exists in the database
            if (Product_categories::where('id', $id)->exists()) {
                $delete_category = Product_categories::findOrFail($id);
                $delete_category->delete();
            }
            // Redirect back with success message
            return redirect()->route('products.category.index')->with('success', 'Product category deleted successfully.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Handle error if the encrypted ID is invalid
            return redirect()->route('products.category.index')->with('error', 'Invalid category ID.');
        } catch (\Exception $e) {
            return redirect()->route('products.category.index')->with('error', 'An error occurred while deleting the category.');
        }
    }
}
