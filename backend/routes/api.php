<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\AuthStoreController;
use App\Http\Controllers\Api\Admin\StoreManagementController;
use App\Http\Controllers\Api\Admin\UserManagementController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-store', [AuthStoreController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{id}', [StoreController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::middleware(['auth:sanctum', 'not.banned', 'role:client,store_owner,admin'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'not.banned', 'role:client'])->group(function () {
    Route::get('/me', [ProfileController::class, 'me']);
    Route::put('/me', [ProfileController::class, 'update']);
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'show']);
    Route::post('/cart/items', [\App\Http\Controllers\Api\CartController::class, 'addItem']);
    Route::put('/cart/items/{productId}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);
    Route::delete('/cart/items/{productId}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);
    Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('/orders/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders/me', [OrderController::class, 'myOrders']);
    Route::get('/orders/me/{orderId}', [OrderController::class, 'showMyOrder']);
});

Route::middleware(['auth:sanctum', 'not.banned', 'role:store_owner'])->group(function () {
    Route::get('/store/me', [StoreController::class, 'myStore']);
    Route::put('/store/me', [StoreController::class, 'upsertMyStore']);

    Route::middleware('store.approved')->group(function () {
        Route::get('/store/products', [ProductController::class, 'myStoreProducts']);
        Route::post('/store/products', [ProductController::class, 'store']);
        Route::put('/store/products/{id}', [ProductController::class, 'update']);
        Route::delete('/store/products/{id}', [ProductController::class, 'destroy']);
    });

    Route::get('/store/orders', [OrderController::class, 'storeOrders']);
    Route::patch('/store/orders/{orderId}/status', [OrderController::class, 'updateStoreOrderStatus']);
});

Route::middleware(['auth:sanctum', 'not.banned', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/ping', function () {
        return response()->json(['message' => 'Admin access granted']);
    });

    Route::get('/users', [UserManagementController::class, 'index']);
    Route::patch('/users/{id}/ban', [UserManagementController::class, 'ban']);
    Route::patch('/users/{id}/unban', [UserManagementController::class, 'unban']);

    Route::get('/stores', [StoreManagementController::class, 'index']);
    Route::patch('/stores/{id}/approve', [StoreManagementController::class, 'approve']);
    Route::patch('/stores/{id}/suspend', [StoreManagementController::class, 'suspend']);
    Route::patch('/stores/{id}/reject', [StoreManagementController::class, 'reject']);
});

