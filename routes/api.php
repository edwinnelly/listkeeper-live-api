<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Businesslist;
use App\Http\Controllers\Api\CustomerListController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\Product_category;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductLocationController;
use App\Http\Controllers\Api\vendors;
use App\Http\Controllers\Api\Purchase_order_items;
use App\Http\Controllers\Api\Purchase_order;
use App\Http\Controllers\Api\PurchaseController;
use App\Models\Product_categories;




use App\Models\User;

Route::get('/hello', function () {
    return "Hello, this is plain text!";
});




// CSRF (no throttle)
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});


Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout']);


// Authenticated routes (no throttle)
Route::middleware(['auth:sanctum'])->group(function () {


    Route::get('/hellos', function () {
        // Get all users
        $users = User::all();

        // Return as JSON
        return response()->json($users);
    });

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
    Route::get('/locations_without_main', [LocationController::class, 'index_without_main']);
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
    Route::post('/customers_add', [CustomerListController::class, 'store']);
    Route::get('/customers/{customerKey}', [CustomerListController::class, 'show']);
    Route::delete('/customers/{id}', [CustomerListController::class, 'destroy']);
    Route::put('/customersupdate/{id}', [CustomerListController::class, 'update']);


    //routes product categories:
    Route::get('/product-categories', [Product_category::class, 'index']);
    Route::post('/add_categories', [Product_category::class, 'storeCategory']);
    Route::put('/updateCategory/{id}', [Product_category::class, 'updateCategory']);
    Route::delete('/delete-categories/{id}', [Product_category::class, 'deleteCategory']);



    //routes Vendors managements:
    Route::get('/vendors', [vendors::class, 'index']);
    Route::post('/add_vendors', [vendors::class, 'store']);
    Route::put('/updatevendors/{id}', [vendors::class, 'update']);
    Route::delete('/vendors-dels/{id}', [vendors::class, 'destroy']);


    //routes product-list managements:
    Route::get('/products', [ProductController::class, 'index']);

    // Route::get('/products', [ProductController::class, 'index']);
    Route::post('/addproducts', [ProductController::class, 'store']);


    Route::get('/products_view/{id}', [ProductController::class, 'show']);


    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/distributeProducts', [ProductController::class, 'distributeProducts']);



    //location products
    Route::get('/product-locations/{id}', [ProductController::class, 'locationproducts']);


    Route::get('/products_locaction_view/{id}', [ProductController::class, 'show']);

    Route::get('/product_history/{id}/{locid}', [ProductController::class, 'product_history']);
    Route::get('/fetch_transfer_stock/{id}', [ProductController::class, 'fetch_transfer_stock']);

      // Get single transfer details for approval
    Route::get('/show_transfer_for_approval/{id}', [ProductController::class, 'show_transfer_for_approval']);


  // Approve or reject a transfer
    Route::post('/approve_transfer/{id}', [ProductController::class, 'approve_transfer']);


    Route::post('/reject_transfer/{id}', [ProductController::class, 'rejectTransfer']);

    // In your routes/api.php file
    Route::get('/product_location_single/{id}/{locid}', [ProductController::class, 'product_location_single']);

    Route::put('/product_location_update/{id}', [ProductController::class, 'update_product']);

    // Create a new stock transfer
    Route::post('/stock-transfers', [ProductController::class, 'transferstock'])
        ->name('transferstock');


    //manage purchase order items
    Route::post('/purchase_order_items', [PurchaseController::class, 'store']);
    Route::get('/product_purchase', [PurchaseController::class, 'purchase_order']);
    Route::get('/purchase-list/{id}', [PurchaseController::class, 'purchaseOrderWithItems']);
    Route::put('/product_purchase_updated/{id}', [PurchaseController::class, 'update']);
    // Approve purchase order
    Route::put('/product_purchase_status/{id}', [PurchaseController::class, 'approve']);

    // Receive items (partial or full)
    Route::put('/purchase-receive/{id}', [PurchaseController::class, 'receiveItems']);

    // Quick receive - mark all as received
    Route::post('/purchase-quick-receive/{id}', [PurchaseController::class, 'quickReceive']);

    // Get receiving summary
    Route::get('/purchase-receiving-summary/{id}', [PurchaseController::class, 'receivingSummary']);
    Route::delete('/product_purchase_delete/{id}', [PurchaseController::class, 'destroy']);


    // Route::post('/purchase_order_items', [Purchase_order_items::class, 'store']);
});
