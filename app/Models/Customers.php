<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
class Customers extends Model
{
     use HasUlids; 
     public function location()
    {
        return $this->belongsTo(Business_locations::class, 'location_id');
    }
}
