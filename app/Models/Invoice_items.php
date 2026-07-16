<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice_items extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'tax',
        'discount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot the model - Auto-calculate total before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Auto-calculate total: (quantity × unit_price) + tax - discount
            $item->total = ($item->quantity * $item->unit_price) + $item->tax - $item->discount;
        });
    }

    /**
     * Relationships
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function business()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product_list::class, 'product_id');
    }

    /**
     * Scope to get items for a specific owner/business
     */
    public function scopeForBusiness($query, $ownerId, $businessKey)
    {
        return $query->where('owner_id', $ownerId)
                     ->where('business_key', $businessKey);
    }

    /**
     * Scope to get items for a specific location
     */
    public function scopeForLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Calculate line total without tax and discount
     */
    public function getLineTotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get the subtotal (before tax and discount)
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get formatted unit price
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 2);
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }
}