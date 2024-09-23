<?php

use App\Http\Controllers\ConfigurationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user('sanctum');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::resource('/configuration', ConfigurationController::class)->where([
        'configuration' => '[0-9]+',
    ]);
});

require __DIR__.'/auth.php';