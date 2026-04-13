<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class purchase_orders extends Model
{
    protected $table = 'purchase_orders';

    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'vendors_id',
        'order_number',
        'order_date',
        'expected_delivery_date',
        'status',

        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'amount_paid',
        'balance_due',

        'payment_status',
        'payment_method',
        'payment_note',

        'notes',
        'signature',
        'attachment',
    ];


    public function items()
    {
        return $this->hasMany(Purchase_order_items::class, 'purchase_order_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendors_id'); // 👈 match your schema
    }

    public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */
    // public function purchaseOrder()
    // {
    //     return $this->belongsTo(Product_list::class, 'product_id');
    // }

    // public function items()
    // {
    //     return $this->hasMany(Purchase_order_items::class, 'purchase_order_id');
    // }

    // public function vendor()
    // {
    //     return $this->belongsTo(Vendor::class, 'vendors_id');
    // }

    // public function location()
    // {
    //     return $this->belongsTo(Business_locations::class, 'location_id');
    // }

    // public function owner()
    // {
    //     return $this->belongsTo(User::class, 'owner_id');
    // }

    /*
    |-------------------------
    | SCOPES (VERY USEFUL)
    |-------------------------
    */

    public function scopeForBusiness($query, $businessKey, $ownerId)
    {
        return $query->where('business_key', $businessKey)
            ->where('owner_id', $ownerId);
    }
}
