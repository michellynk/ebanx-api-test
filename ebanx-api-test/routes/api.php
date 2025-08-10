<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/reset', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
