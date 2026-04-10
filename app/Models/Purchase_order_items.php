<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase_order_items extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];
}