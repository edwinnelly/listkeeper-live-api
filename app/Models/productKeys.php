<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
class productKeys extends Model
{
    use HasUlids;
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
