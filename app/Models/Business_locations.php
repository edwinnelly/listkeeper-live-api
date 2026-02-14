<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business_locations extends Model
{
    //
    protected $fillable = [
        'owner_id',
        'business_key',
        'location_id',
        'location_status',
        'location_name',
        'address',
        'city',
        'state',
        'head_office',
        'phone',
        'country',
        'postal_code',
        'status',
        'manager_id',
    ];

    // Relationships (Optional but Recommended)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function business()
    {
        return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // public function users_locations()
    // {
    //     return $this->hasMany(User::class, 'locations');
    // }
}
