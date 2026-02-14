<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Businesslist;
use App\Http\Controllers\Api\CustomerListController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\Product_category;
use App\Models\Product_categories;

// CSRF (no throttle)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout']);


// Authenticated routes (no throttle)
Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function () {
        return auth()->user()->load('user_roles')->load('businesses_one');
    });

    // Business routes
    Route::get('/businesses', [Businesslist::class, 'index']);
    Route::post('/newbusinesses', [Businesslist::class, 'store']);
    Route::get('/businessinfo/{id}', [Businesslist::class, 'business_details']);
    Route::post('/switchBusiness/{id}', [Businesslist::class, 'switchBusiness']);
    Route::put('/updatebusiness/{id}', [Businesslist::class, 'updatebusiness']);
    Route::delete('/deletebusiness/{id}', [Businesslist::class, 'deleteBusiness']);
    Route::put('/suspendBusiness/{id}', [Businesslist::class, 'suspendBusiness']);

    // Location routes
    Route::get('/locations', [LocationController::class, 'index']);
    Route::post('/locationsadd', [LocationController::class, 'store']);
    Route::put('/locationsupdate/{id}', [LocationController::class, 'update']);
    Route::delete('/locationsdel/{id}', [LocationController::class, 'destroy']);

    // Users list
    Route::get('/users', [UserListController::class, 'index']);
    Route::get('/userslocations/{id}', [UserListController::class, 'users_locations']);
    Route::post('/usersadd', [UserListController::class, 'store']);
    Route::delete('/usersdel/{id}', [UserListController::class, 'destroy']);
    Route::put('/usersupdate/{id}', [UserListController::class, 'update']);
    Route::get('/usersinfo/{id}', [UserListController::class, 'show']);
    Route::get('/usersroles/{id}', [UserListController::class, 'roles']);
    Route::post('/permissions_update/{id}', [UserListController::class, 'updateRoles']);

    //Customers List
    Route::get('/customers', [CustomerListController::class, 'index']);


    //routes product categories:
    Route::get('/product-categories', [Product_category::class, 'index']);
    Route::post('/add_categories', [Product_category::class, 'storeCategory']);
    Route::post('/product-categories/{id}', [Product_category::class, 'update']);
    Route::delete('/product-categories/{id}', [Product_category::class, 'destroy']);
   
});
