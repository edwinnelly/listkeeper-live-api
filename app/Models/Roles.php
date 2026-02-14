<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    //
      protected $fillable = [
        'user_id',
        'business_key',
        'location_id',
        'owner_id',
    ];
}
