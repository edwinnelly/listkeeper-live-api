<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
  
class Business_list extends Model
{
  use HasUlids; 
 // Fields that are mass assignable
    protected $fillable = [
        'owner_id',
        'business_key',
        'business_name',
        'slug',
        'registration_no',
        'industry_type',
        'email',
        'currency',
        'website',
        'about_business',
        'phone',
        'address',
        'country',
        'subscription_type',
        'subscription_plan',
        'language',
        'state',
        'city',
        'logo',
        'status',
    ];


    public function subscription()
    {
        return $this->hasOne(Subscriptions::class, 'business_key', 'business_key');
    }
}
