<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasUlids;
    //
    public function business()
{
    return $this->belongsTo(Business_list::class, 'business_key', 'business_key');
}

}
