<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BusinessListController;
use App\Http\Controllers\BusinessLocationsController;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\ProductListController;
use App\Http\Controllers\ProductUnitsController;
use App\Http\Controllers\UserController;
use App\Models\Business_locations;
use App\Models\Product_categories;
use App\Models\User;
use Faker\Core\Uuid;
use Illuminate\Support\Str;



Route::get('/', function () {
    return view('welcome');
});


Auth::routes();

Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::post('/logout', function () {
    Auth::logout(); // Logs the user out
    request()->session()->invalidate(); // Invalidate the session
    request()->session()->regenerateToken(); // Regenerate CSRF token
    return redirect('/login'); // Redirect to login page
})->name('logout');

Route::controller(BusinessListController::class)->group(function () {

    Route::get('/accounts', 'index')->name('business.accounts.index');         // List all accounts
    Route::get('/accounts/{id}', 'show')->name('business.accounts.show');      // Show a specific account
    Route::get('/switches/{id}', 'switch')->name('business.accounts.switch');      // Show a specific account
    Route::post('/accounts', 'store')->name('business.accounts.store');        // Create a new account

    Route::put('/accounts/{id}', 'update')->name('business.accounts.update');  // Update an account
    Route::delete('/accounts/{id}', 'destroy')->name('business.accounts.destroy'); // Delete an account

    Route::put('/switches', 'switchBusiness')->name('business.accounts.switchBusiness');        // change account

});


// Routes for managing employee accounts and assigning roles
Route::controller(UserController::class)->group(function () {
    // Display a list of all employee accounts
    Route::get('/employee', 'index')->name('users.account.users');
    // Store a newly created employee account
    Route::post('/employee', 'store')->name('users.account.store');
    // Show the form for editing an existing employee account
    Route::get('/employee_map/{id}', 'edit')->name('users.account.edit');
    // Show the form for update an existing employee account
    Route::get('/employee_maps/{id}', 'update')->name('users.account.update');
    Route::put('/employee_maps/{id}', 'updatedProfile')->name('account.updated');
    // Delete an existing employee account
    Route::get('/employee_auth/{id}', 'delete_users')->name('users.account.delete_users');
    Route::delete('/employee_auth/{id}', 'destroy')->name('users.account.destroy');
    // Display the role assignment form for a specific user
    Route::get('/primary-roles/{id}', 'account_roles')->name('lusers.account.account_roles');
    // Update role permissions for a specific user
    Route::put('/primary-roles/{id}', 'setPermssions')->name('account.setPermssions');
});



//manage the business locations
Route::controller(BusinessLocationsController::class)->group(function () {
    Route::get('/el', 'index')->name('location.accounts.index');
    Route::get('el/{id}', 'show')->name('location.accounts.show'); //view location profile before edit
    Route::post('/locations', 'store')->name('location.accounts.store');
    Route::put('el/{id}', 'update')->name('location.accounts.update');
    // Delete an existing location account
    Route::get('/l_auth/{id}', 'delete_location')->name('location.account.delete');
    Route::delete('/l_auth/{id}', 'destroy')->name('location.account.destroy');
});

//manage the products
Route::controller(ProductListController::class)->group(function () {
    // Product list (all products)

    Route::get("/ZYbNBASmYPYuG7ZNYeQLgSvWgmjaWamjLUXDt2vDEwg", 'index')->name('products.index');

    // Get products under a specific sub-location
    Route::get('/rfks1oTmGcMjYrAlQzx8OMmix0Rq2EiRFPKBon9NkME', 'sublocation_products')->name('sublocation_products.items');

    // Show form to add new product
    Route::get('/aMY00rFM7mM8e9JvUtxOcX7epil6oFaZwoOCwU43BG0', 'create')->name('products.add');

    // Store new product
    Route::post('/1cOcYfzU16lY8LjvkAFeUzu0K4hlYeA8N4mizlkzKY', 'store')->name('products.store');

    // Show a single product for editing
    Route::get('/x1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV/{id}', 'show')->name('products.edit.show');

    // Update product details
    Route::put('x1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV/{id}', 'update')->name('products.edit.update');

    // Get the product keys (serial numbers) for a product
    Route::get('/Vl4vP1VrTkQx1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV/{id}', 'product_keys')->name('product.serials.show');

    // Store a new serial number for a product
    Route::post('/soiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV', 'serials_store')->name('product.serials.store');

    // View history of product key usage (serial tracking)
    Route::get('/QVl4vP1VrTkQx1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV/{id}/{pid}', 'view_product_keys_history')->name('product.serials.history');

    // Update product key history (e.g., status changes)
    Route::put('x1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV1A/{id}', 'update_key_history')->name('products.keys.update');

    // Fetch all product keys across products
    Route::get('/ox1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV1A', 'product_keys_all')->name('products.all.keys');

    Route::get('/Pox1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV1A', 'product_search')->name('products.subaccount.add');

    Route::get('/foods/search', 'search')->name('products.search.adds');

     // Show a single product for adding to sub store
    Route::get('/Zcx1VrTkQ5Z0Vl4vP1VrTkQ5Z0VlSTV/{id}', 'show')->name('products.edit.show');


});


//manage product category
Route::controller(ProductCategoriesController::class)->group(function () {
    //product list
    Route::get('/pcsoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV', 'index')->name('products.category.index');
    Route::post('/pcssoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV', 'store')->name('product-categories.store');
    Route::get('/pcdsoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV/{id}', 'delete_pro_category')->name('product.categories.delete');
    Route::delete('/pcdssoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV/{id}', 'destroy')->name('product.categories.destroy');
    Route::get('/pelsoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV/{id}', 'show')->name('product.categories.show');
    Route::get('/pelssoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV/{id}', 'showProducts')->name('product.categories.moreproducts');

    Route::put('pcusoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV/{id}', 'update')->name('product.categories.update');
});

//manage product unit
Route::controller(ProductUnitsController::class)->group(function () {
    //product list
    Route::get('/puusoiMmNYK2RPRW4vT1VrTkQ5Z0VlSTV', 'index')->name('products.unit.index');
});
