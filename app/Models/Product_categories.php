<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;

// class Product_categories extends Model
// {
//     //


//     protected $fillable = [
//         'name',
//         'slug',
//         'description',

//     ];

//    public function products()
//     {
//         return $this->hasMany(Product_list::class, 'category_id');
//     }
// }


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product_categories extends Model
{
    // use HasUuids;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'business_key',
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product_list::class, 'category_id');
    }
}
