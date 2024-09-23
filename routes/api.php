<?php

use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ShippingInfoController;
use App\Http\Controllers\VnpayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user('sanctum');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/configuration', ConfigurationController::class)->where([
        'configuration' => '[0-9]+',
    ]);

Route::prefix('shipping')->group(function () {
    Route::get('/country', [ShippingInfoController::class, 'country']);
    Route::get('/province', [ShippingInfoController::class, 'province']);
    Route::get('/district/{provinceCode}', [ShippingInfoController::class, 'district']);
    Route::get('/cost', [ShippingInfoController::class, 'cost']);
});

require __DIR__.'/auth.php';