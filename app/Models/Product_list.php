<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_list extends Model
{
    
    public function products()
    {
        return $this->hasMany(Product_list::class);
    }
    protected $fillable = [
        'owner_id',
        'business_key',
        'name',
        'sku',
        'description',
        'slug',
        'dimensions',
        'category_id',
        'sub_category_id',
        'child_sub_category_id',
        'product_measurements',
        'price',
        'cost_price',
        'sale_price',
        'stock_quantity',
        'low_stock_threshold',
        'discount_percentage',
        'discount_start_date',
        'discount_end_date',
        'manufactured_at',
        'expires_at',
        'weight',
        'length',
        'width',
        'height',
        'supplier_id',
        'is_active',
        'is_featured',
        'is_on_sale',
        'is_out_of_stock',
        'image',
        'additional_info',
        'barcode'
    ];

    //product with category
    public function category()
    {
        return $this->belongsTo(Product_categories::class, 'category_id');
    }

    public function vendors()
    {
        return $this->belongsTo(Vendors::class, 'supplier_id');
    }
    public function business_lists()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    public function unit()
    {
        return $this->belongsTo(Product_units::class);
    }

    public function productkeys()
    {
        return $this->belongsTo(productKeys::class);
    }

}
