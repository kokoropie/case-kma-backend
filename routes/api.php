<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ConfigController as AdminConfigController;
use App\Http\Controllers\Admin\Config\ColorController as AdminColorController;
use App\Http\Controllers\Admin\Config\ModelController as AdminModelController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\ShippingInfoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group( function () {
    Route::get('/user', function (Request $request) {
        return $request->user('sanctum');
    });
    
    Route::middleware(['verified'])->group(function () {
        Route::resource('/configuration', ConfigurationController::class)->where([
            'configuration' => '[0-9]+',
        ])->except(['create']);

        Route::resource('/address', ShippingAddressController::class)->whereUuid('address')->except(['create', 'edit']);
        Route::resource('/order', OrderController::class)->whereUuid('order')->except(['create', 'edit']);
        
        Route::prefix('/payment')->group(function () {
            Route::post('/', [PaymentController::class, 'store']);
        });

        Route::middleware(['admin'])->prefix('/admin')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index']);
            Route::resource('/order', AdminOrderController::class)->whereUuid('order')->except(['create', 'edit']);
            Route::resource('/user', AdminUserController::class)->except(['create', 'edit']);
            Route::prefix('/config')->group(function () {
                Route::resource('/color', AdminColorController::class)->except(['create', 'edit']);
                Route::resource('/model', AdminModelController::class)->except(['create', 'edit']);
            });
            Route::resource('/config', AdminConfigController::class)->except(['create', 'edit']);
        });
    });
});

Route::resource('/configuration', ConfigurationController::class)->where([
    'configuration' => '[0-9]+',
])->only(['create']);

Route::prefix('shipping')->group(function () {
    Route::get('/country', [ShippingInfoController::class, 'country']);
    Route::get('/province', [ShippingInfoController::class, 'province']);
    Route::get('/district/{provinceCode}', [ShippingInfoController::class, 'district']);
    Route::get('/cost', [ShippingInfoController::class, 'cost']);
});


Route::get('/{payment}-return', [PaymentController::class, 'return']);

require __DIR__.'/auth.php';