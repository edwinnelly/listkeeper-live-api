<?php

namespace App\Models;

use App\Http\Controllers\Api\Product_category;
use Illuminate\Database\Eloquent\Model;

class LocationProductList extends Model
{
    protected $table = 'location_product_lists';

    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'product_id',
        'category_id',
        'supplier_id',
        'price',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'owner_id' => 'integer',
        'location_id' => 'integer',
        'product_id' => 'integer',
        'category_id' => 'integer',
        'supplier_id' => 'integer',
        'price' => 'float',
        'cost_price' => 'float',
        'sale_price' => 'float',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Get the product associated with this location product
     */
    public function product()
    {
        return $this->belongsTo(Product_list::class, 'product_id');
    }

    /**
     * Get the category associated with this location product
     */
    public function category()
    {
        return $this->belongsTo(Product_categories::class, 'category_id');
    }

    /**
     * Get the supplier/vendor associated with this location product
     */
    public function supplier()
    {
        return $this->belongsTo(Vendor::class, 'supplier_id');
    }

    /**
     * Get the location associated with this product
     */
    public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }

    /**
     * Get the owner/user associated with this record
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the business associated with this record via business_key
     */
    public function business()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }
}