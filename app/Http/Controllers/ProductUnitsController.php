<?php

namespace App\Http\Controllers;

use App\Models\Product_units;
use Illuminate\Http\Request;

class ProductUnitsController extends Controller
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
        $fetch_all = Product_units::get();
        return view('products_units.products_units',compact('fetch_all'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Product_units $product_units)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product_units $product_units)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product_units $product_units)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product_units $product_units)
    {
        //
    }
}
