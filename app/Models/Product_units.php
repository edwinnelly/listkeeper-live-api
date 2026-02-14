<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product_units extends Model
{
    use HasFactory;

    protected $table = 'product_units';

    protected $fillable = [
        'owner_id',
        'business_key',
        'name',
        'short_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the owner of the product unit.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the business that owns the product unit.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessList::class, 'business_key', 'business_key');
    }

    /**
     * Get the products that use this unit.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    /**
     * Scope for active units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for business units.
     */
    public function scopeForBusiness($query, $businessKey)
    {
        return $query->where('business_key', $businessKey);
    }
}