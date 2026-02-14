<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationProductList extends Model
{
    //
     public function locationProducts()
    {
        return $this->belongsTo(Product_list::class, 'product_id');
    }
     public function locationProducts_unit()
    {
        return $this->belongsTo(Product_units::class, 'unit_id');
    }

    public function locationProducts_category()
    {
        return $this->belongsTo(Product_categories::class, 'category_id');
    }
     public function locationProducts_vendors()
    {
        return $this->belongsTo(Vendors::class, 'supplier_id');
    }
}
