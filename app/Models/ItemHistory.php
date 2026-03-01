<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
   protected $table='item_histories';

    protected $fillable = [
        // Foreign Keys
        'product_id',
        'owner_id',
        'business_key',
        'location_id',
        
        // Transaction Info
        'type',
        'quantity',
        'cost',
        'price',
        
        // Polymorphic source
        'source_id',
        'source_type',
        
        // Additional fields
        'note',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'cost' => 'decimal:4',
        'price' => 'decimal:4',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = [
        'transaction_date',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the product associated with this history
     */
    public function product()
    {
        return $this->belongsTo(Product_list::class, 'product_id');
    }

    /**
     * Get the owner/user associated with this history
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the location associated with this history
     */
    public function location()
    {
        return $this->belongsTo(Business_list::class, 'location_id');
    }

    /**
     * Get the business associated with this history via business_key
     */
    public function business()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    /**
     * Get the source model (polymorphic relationship)
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include histories for a specific business
     */
    public function scopeForBusiness($query, $businessKey)
    {
        return $query->where('business_key', $businessKey);
    }

    /**
     * Scope a query to only include histories at a specific location
     */
    public function scopeAtLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope a query to only include histories for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include histories of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include histories within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include histories from today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', now()->toDateString());
    }

    /**
     * Scope a query to only include histories from this week
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope a query to only include histories from this month
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('transaction_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    /**
     * Get the total quantity for positive transactions (additions)
     */
    public function scopeTotalIn($query)
    {
        return $query->where('quantity', '>', 0)->sum('quantity');
    }

    /**
     * Get the total quantity for negative transactions (removals)
     */
    public function scopeTotalOut($query)
    {
        return $query->where('quantity', '<', 0)->sum('quantity');
    }

    /**
     * Check if this is an incoming transaction
     */
    public function isIncoming(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if this is an outgoing transaction
     */
    public function isOutgoing(): bool
    {
        return $this->quantity < 0;
    }

    /**
     * Get the absolute quantity (always positive)
     */
    public function getAbsoluteQuantityAttribute()
    {
        return abs($this->quantity);
    }

    /**
     * Get formatted quantity with sign
     */
    public function getFormattedQuantityAttribute()
    {
        $sign = $this->quantity > 0 ? '+' : ($this->quantity < 0 ? '-' : '');
        return $sign . number_format(abs($this->quantity), 4);
    }

    /**
     * Get the total value (quantity * price)
     */
    public function getTotalValueAttribute()
    {
        if ($this->price) {
            return $this->quantity * $this->price;
        }
        return null;
    }

    /**
     * Get the total cost (quantity * cost)
     */
    public function getTotalCostAttribute()
    {
        if ($this->cost) {
            return $this->quantity * $this->cost;
        }
        return null;
    }
}
