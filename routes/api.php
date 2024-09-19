<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/users', function () {
    return \App\Models\User::paginate();
});

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
