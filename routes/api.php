<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VapiController;

Route::post('vapi/get-menu', [VapiController::class, 'getMenu']);
Route::post('vapi/create-order', [VapiController::class, 'createOrder']);
Route::match(['get','post'],'vapi/track-order/{order?}', [VapiController::class, 'trackOrder']);
Route::post('vapi/assistant-event', [VapiController::class, 'assistantEvent']);
