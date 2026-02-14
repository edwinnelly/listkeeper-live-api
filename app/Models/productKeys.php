<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class productKeys extends Model
{
    //

    protected $fillable = [
        'serial_number',
        'status',
        'assigned_to',
        'purchase_date',
        'sale_date',
        'product_id',
        'owner_id',
        'business_key',
        'location_id',
        'username',
    ];
}
