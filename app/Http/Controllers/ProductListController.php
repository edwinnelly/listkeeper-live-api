<?php

namespace App\Http\Controllers;

use App\Models\Business_list;
use App\Models\Business_locations;
use App\Models\Product_list;
use App\Models\Product_categories;
use App\Models\Product_units;
use App\Models\Productkeyhistory;
use App\Models\LocationProductList;
use App\Models\productKeys;
use App\Models\User;
use App\Models\Vendors;
use Illuminate\Container\Attributes\Cache;
// use Illuminate\Container\Attributes\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductListController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businessKey = auth()->user()->active_business_key;
        $fetchProducts = Product_list::with(['category', 'vendors', 'unit', 'business_lists'])
            ->where('business_key', $businessKey)
            ->get();
        return view('product_list.products_listings', compact('fetchProducts'));
    }

    //  public function sublocation_products()
    // {
    //     $businessKey = auth()->user()->active_business_key;
    //     $location_id = auth()->user()->locations;
    //     $owner_id = auth()->user()->id;

    //     $fetchProducts = LocationProductList::with('locationProducts')->where('location_id',$location_id)->where('business_key',$businessKey)->where('owner_id',$owner_id)->get();

    //     $getlocation_data = Business_locations::where('id',$location_id)->where('business_key',$businessKey)->where('owner_id',$owner_id)->firstOrFail();

    //     return view('product_list.products_listings_location', compact('fetchProducts','getlocation_data'));

    // }


    // public function sublocation_products()
    // {
    //     $businessKey = auth()->user()->active_business_key;
    //     $location_id = auth()->user()->locations; // Assuming this holds the location id
    //     $owner_id = auth()->user()->id;



    //     // ✅ Check if user has a location assigned
    //     if (empty($location_id)) {
    //         return redirect()->back()->with('error', 'No location assigned to your account.');
    //     }

    //     // ✅ Check if location exists for this user in users table
    //     $getuser_data = User::where('id', $owner_id)
    //         ->where('active_business_key', $businessKey)
    //         ->where('locations', $location_id)
    //         ->first();



    //     if (!$getuser_data) {
    //         return redirect()->back()->with('error', 'Your assigned location was not found.');
    //     }

    //     // ✅ Check if location also exists in business_locations
    //     $getlocation_data = Business_locations::where('business_key', $businessKey)
    //         ->where('owner_id', $owner_id)
    //         ->firstOrFail();

    //         $getBusinessInfo = Business_list::where('business_key',$businessKey)->where('owner_id',$owner_id)->first();

    //         // dd($getBusinessInfo);



    //     // ✅ Fetch products for the location
    //     $fetchProducts = LocationProductList::with('locationProducts')->with('locationProducts_unit')->with('locationProducts_category')
    //         ->where('location_id', $location_id)
    //         ->where('business_key', $businessKey)
    //         ->where('owner_id', $owner_id)
    //         ->get();


    //     return view('product_list.products_listings_location', compact('fetchProducts', 'getlocation_data','getBusinessInfo'));
    // }


    public function sublocation_products()
    {
        $user = auth()->user();
        $businessKey = $user->active_business_key;
        $locationId = $user->locations; // Assuming this holds the location id
        $ownerId = $user->id;

        // ✅ Check if user has a location assigned
        if (empty($locationId)) {
            return redirect()->back()->with('error', 'No location assigned to your account.');
        }

        // ✅ Verify user’s assigned location exists
        $userData = User::where([
            'id' => $ownerId,
            'active_business_key' => $businessKey,
            'locations' => $locationId,
        ])->first();

        if (!$userData) {
            return redirect()->back()->with('error', 'Your assigned location was not found.');
        }

        // ✅ Verify location exists in business_locations
        $locationData = Business_locations::where([
            'business_key' => $businessKey,
            'owner_id' => $ownerId,
        ])->firstOrFail();

        // ✅ Fetch business info
        $businessInfo = Business_list::where([
            'business_key' => $businessKey,
            'owner_id' => $ownerId,
        ])->first();

        // ✅ Fetch products for the location (with relationships)
        $products = LocationProductList::with(['locationProducts', 'locationProducts_unit', 'locationProducts_category'])
            ->where([
                'location_id' => $locationId,
                'business_key' => $businessKey,
                'owner_id' => $ownerId,
            ])->get();

        return view('product_list.products_listings_location', compact('products', 'locationData', 'businessInfo'));
    }




    public function product_keys($id)
    {
        $id = Crypt::decrypt($id);
        $businessKey = auth()->user()->active_business_key;

        // get the product details safely
        $productDetails = Product_list::findOrFail($id);

        // get serials related to product and business
        $serials = productKeys::where('business_key', $businessKey)
            ->where('product_id', $id)
            ->get();

        return view('product_list.products_keys', compact('serials', 'productDetails'));
    }

    public function product_keys_all()
    {
        $businessKey = auth()->user()->active_business_key;
        // get serials related to product and business
        $serials = productKeys::where('business_key', $businessKey)->get();
        return view('product_list.products_keys_all', compact('serials'));
    }

    public function product_search()
    {
        $businessKey = auth()->user()->active_business_key;
        // get serials related to product and business
        $products = Product_list::where('business_key', $businessKey)->get();
        return view('product_list.products_listings_location_search', compact('products'));
    }



    public function view_product_keys_history($id, $pid)
    {
        try {
            // decrypt ids (make sure both were encrypted in the route)
            $id = Crypt::decrypt($id);
            $pid = Crypt::decrypt($pid); // remove if pid was not encrypted

            $businessKey = auth()->user()->active_business_key;

            // get the product details using pid
            $productDetails = Product_list::findOrFail($pid);

            // get the specific serial key for this product
            $serial = ProductKeys::where('product_id', $pid)
                ->where('business_key', $businessKey)
                ->where('id', $id)
                ->firstOrFail();

            return view('product_list.products_keys_history', compact('serial', 'productDetails'));
        } catch (\Exception $e) {
            // handle error gracefully
            return redirect()->back()->with('error', 'Invalid request or record not found.');
        }
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 1. Get the business_key of the currently authenticated user
        $businessKey = auth()->user()->active_business_key;

        // 2. Get the authenticated user's ID (owner of the business/product)
        $ownerId = auth()->id();

        // 3. Fetch product categories that belong to this business
        $category = Product_categories::where('business_key', $businessKey)->get();

        // 4. Fetch vendors that belong to this business
        $vendors = Vendors::where('business_key', $businessKey)->get();

        // 5. Fetch all product units (e.g. kg, litre, box, piece)
        $units = Product_units::all();

        // 6. Send the fetched data to the 'product_add' Blade view
        return view('product_list.product_add', compact('category', 'vendors', 'units'));
    }

    public function serials_store(Request $request)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:255|unique:product_keys,serial_number',
            'status' => 'nullable|in:available,sold,reserved,returned,defective',
            'product_id' => 'nullable|string',
        ]);

        if (!empty($validated['product_id'])) {
            $validated['product_id'] = Crypt::decrypt($validated['product_id']);
        }

        DB::beginTransaction();
        try {
            $businessKey = auth()->user()->active_business_key;
            $ownerId = auth()->id();

            // Add custom values
            $validated['business_key'] = $businessKey;
            $validated['owner_id'] = $ownerId;
            $validated['location_id'] = 1;

            productKeys::create($validated);

            DB::commit();

            // Refresh same page
            return redirect()->back()->with('success', 'Serial added successfully!');
            // Or go to products list:
            // return redirect()->route('products.index')->with('success', 'Serial added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create serial: ' . $e->getMessage());
        }
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:product_lists,sku',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit_id' => 'required|exists:product_units,id',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'discount_start_date' => 'nullable|date',
            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:manufactured_at',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'weight' => 'nullable|string|max:50',
            'dimensions' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:vendors,id',
            'status' => 'required|in:active,inactive,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1048',
        ]);

        DB::beginTransaction();

        try {
            $businessKey = auth()->user()->active_business_key;
            $ownerId = auth()->id();

            // Add custom values
            $validated['business_key'] = $businessKey;
            $validated['owner_id'] = $ownerId;
            $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6); // Unique + readable
            $validated['price'] = $validated['sale_price'] ?? 0;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('product_images', 'public');
                $validated['image_path'] = $imagePath;
            }

            Product_list::create($validated);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create product.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Product_list $product_list, $id)
    {
        $businessKey = auth()->user()->active_business_key;
        // Decrypt the ID if it's encrypted
        $id = Crypt::decrypt($id);
        $product = Product_list::with('category')->with('vendors')->with('unit')->where('id', operator: $id)->first();
        $category = Product_categories::where('business_key', $businessKey)->get();
        $vendors = Vendors::where('business_key', $businessKey)->get();
        $units = Product_units::get();

        return view('product_list.product_edit', compact('product', 'category', 'units', 'vendors'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product_list $product_list)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updates(Request $request, Product_list $product_list)
    {
        //

    }

    public function update(Request $request, $id)
    {
        $businessKey = auth()->user()->active_business_key;

        $id = Crypt::decrypt($id);
        $product = Product_list::where('id', $id)->where('business_key', $businessKey)
            ->firstOrFail();

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:product_lists,sku,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:product_categories,id',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit_id' => 'required|exists:product_units,id',
            'stock_quantity' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'manufactured_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:manufactured_at',
            'weight' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:vendors,id',
            'status' => 'required|in:active,inactive,draft',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1048',
        ]);

        try {
            DB::beginTransaction();

            if ($request->hasFile('image')) {
                // delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $validated['image'] = $request->file('image')->store('product_images', 'public');
            }


            // Update product
            $product->update($validated);

            DB::commit();

            return redirect()->route('products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function update_key_history(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $businessKey = auth()->user()->active_business_key;
        $active_user_id = auth()->user()->id;
        $active_name = auth()->user()->name;

        // Fetch the product key only if it belongs to this business
        $product = ProductKeys::where('id', $id)
            ->where('business_key', $businessKey)
            ->firstOrFail();

        // Validate request
        $validated = $request->validate([
            'status' => 'required|in:available,sold,reserved,returned,defective',
        ]);
        //add logined users
        $validated['assigned_to'] = $active_user_id;
        $validated['username'] = $active_name;


        try {
            DB::beginTransaction();
            // Update product
            $product->update($validated);

            DB::commit();

            return redirect()->route('product.serials.show', [
                // 'id'  => Crypt::encrypt($product->id),
                'id' => Crypt::encrypt($product->product_id),
            ])->with('success', 'Product serial updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function search(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        // Use validated value
        $query = $validated['q'];

        // Search with case-insensitive match (ILIKE works in PostgreSQL)
        $product = Product_list::where('name', 'ILIKE', "%{$query}%")->get();

        // Encrypt IDs before sending to frontend
        $results = $product->map(function ($items) {
            return [
                'id' => Crypt::encrypt($items->id),
                'name' => $items->name,
                'description' => $items->description,
            ];
        });

        return response()->json($results);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product_list $product_list)
    {
        //
    }
}
