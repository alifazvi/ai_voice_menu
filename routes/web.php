<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('restaurants', RestaurantController::class)->only(['index','create','store']);
Route::resource('menus', MenuController::class)->only(['index','create','store']);
Route::resource('customers', CustomerController::class)->only(['index','create','store']);
Route::resource('orders', OrderController::class)->only(['index','create','store']);
