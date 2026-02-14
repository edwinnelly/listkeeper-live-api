<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
     public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }
}
