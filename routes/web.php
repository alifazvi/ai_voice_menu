<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DineInController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('restaurants', RestaurantController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('menus', MenuController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::match(['post', 'patch'], '/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('dineins', DineInController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
