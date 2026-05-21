<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
class Purchase_order_items extends Model
{
    use HasUlids;
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

    public function purchaseOrder()
    {
        return $this->belongsTo(purchase_orders::class, 'purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product_list::class, 'product_id');
    }
}
