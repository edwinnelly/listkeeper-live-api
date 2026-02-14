<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',

        'vendor_name',
        'contact_person',
        'email',
        'phone',

        'address',
        'city',
        'state',
        'country',
        'postal_code',

        'industry',
        'tax_id',
        'registration_number',
        'website',

        'bank_name',
        'bank_account_number',
        'bank_account_name',

        'is_active',
        'notes',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Vendor belongs to a user (owner)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Vendor belongs to a business (via business_key)
     */
    public function business()
    {
        return $this->belongsTo(BusinessList::class, 'business_key', 'business_key');
    }

    /**
     * Vendor belongs to a business location
     */
    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (Recommended)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope vendors by business
     */
    public function scopeForBusiness($query, string $businessKey)
    {
        return $query->where('business_key', $businessKey);
    }

    /**
     * Scope vendors by owner
     */
    public function scopeForOwner($query, int $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }
}
