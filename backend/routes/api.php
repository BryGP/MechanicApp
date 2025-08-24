<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Endpoints principales
Route::apiResource('products', ProductController::class);
Route::apiResource('orders', OrderController::class);