<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DineInController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('restaurants', RestaurantController::class)->only(['index','create','store','destroy']);
Route::resource('menus', MenuController::class)->only(['index','create','store','destroy']);
Route::resource('customers', CustomerController::class)->only(['index','create','store','destroy']);
Route::resource('orders', OrderController::class)->only(['index','create','store','destroy']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::resource('dineins', DineInController::class)->only(['index','create','store','destroy']);
