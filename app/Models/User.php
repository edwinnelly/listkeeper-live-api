<?php

namespace App\Models;

use App\Http\Controllers\Api\Businesslist;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 👈 Add this
use App\Models\Business_list;
use App\Models\Business_locations;
use App\Models\Roles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 👈 Include Sanctum

    protected $fillable = [
        'name',
        'email',
        'password',
        'creator',
        'business_key',
        'active_business_key',
        'profile_pic',
        'locations',
        'phone_number',
        'role',
        'address',
        'country',
        'city',
        'state',
        'about',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ─── RELATIONSHIPS ───────────────────────────────
    public function businesses()
    {
        return $this->hasMany(Business_list::class, 'owner_id');
    }

    public function businessLocations()
    {
        return $this->hasMany(Business_locations::class, 'business_key', 'active_business_key');
    }

    public function user_roles()
    {
        return $this->hasOne(Roles::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }


    public function roles()
    {
        return $this->hasMany(Roles::class);
    }


    public function businesses_one()
    {
        return $this->hasMany(Business_list::class, 'business_key', 'business_key');
    }

    // public function hasPermission($permission)
    // {
    //     foreach ($this->roles as $role) {
    //         if ($role->$permission === 'yes') {
    //             return true;
    //         }
    //     }
    //     return false;
    // }
    // public function hasPermission($permission, $businessKey = null)
    // {
    //     $businessKey = $businessKey ?? $this->active_business_key;

    //     // Make sure the user has roles for this business
    //     $roles = $this->roles()->where('business_key', $businessKey)->get();

    //     foreach ($roles as $role) {
    //         if ($role->$permission === 'yes') {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    public function hasPermission(string $permission, ?string $businessKey = null): bool
    {
        $businessKey = $businessKey ?? $this->active_business_key;

        if (!$businessKey) {
            return false;
        }

        return $this->roles()
            ->where('business_key', $businessKey)
            ->where($permission, 'yes')
            ->exists();
    }


    public function location()
    {
        return $this->belongsTo(Business_locations::class, 'locations');
    }
}
