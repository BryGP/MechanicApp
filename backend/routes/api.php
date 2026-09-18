<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes — MechanicApp
|--------------------------------------------------------------------------
|
| All routes defined here are automatically prefixed with /api by Laravel.
| They are stateless (no session/cookie) and return JSON responses.
|
| Auth: Currently open (no authentication middleware).
|       Sanctum is installed and can be enabled per-route with:
|       ->middleware('auth:sanctum')
|
*/

/**
 * Auth check route (requires Sanctum token).
 * Returns the currently authenticated user object.
 * Not actively used by the frontend yet.
 */
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * Health check endpoint.
 * Used to verify the API server is running and reachable.
 * Response: { "status": "ok" }
 */
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

/*
|--------------------------------------------------------------------------
| Resource Routes
|--------------------------------------------------------------------------
|
| apiResource() generates 5 RESTful endpoints per resource (no create/edit
| HTML form routes, since this is a pure API):
|
|  Products:
|   GET    /api/products          ? ProductController@index
|   POST   /api/products          ? ProductController@store
|   GET    /api/products/{id}     ? ProductController@show
|   PUT    /api/products/{id}     ? ProductController@update
|   DELETE /api/products/{id}     ? ProductController@destroy
|
|  Orders:
|   GET    /api/orders            ? OrderController@index
|   POST   /api/orders            ? OrderController@store
|   GET    /api/orders/{id}       ? OrderController@show
|   PUT    /api/orders/{id}       ? OrderController@update
|   DELETE /api/orders/{id}       ? OrderController@destroy
|
*/
Route::apiResource('products', ProductController::class);
Route::apiResource('orders', OrderController::class);
