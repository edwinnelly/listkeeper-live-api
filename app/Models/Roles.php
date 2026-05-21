<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
class Roles extends Model
{
  use HasUlids;
    //
      protected $fillable = [
        'user_id',
        'business_key',
        'location_id',
        'owner_id',
    ];
}
