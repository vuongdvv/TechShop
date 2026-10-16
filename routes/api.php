<?php

use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\AdminCustomerController;
use App\Http\Controllers\Api\V1\AdminCategoryController;
use App\Http\Controllers\Api\V1\AdminOrderController;
use App\Http\Controllers\Api\V1\AdminProductController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
        Route::get('/password-resets/{token}', [PasswordResetController::class, 'validateToken']);
        Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    });

    Route::middleware('techshop.user')->group(function (): void {
        Route::post('/cart/items', [CartController::class, 'store']);
        Route::patch('/cart/items/{cartItemId}', [CartController::class, 'update']);
        Route::delete('/cart/items/{cartItemId}', [CartController::class, 'destroy']);

        Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);
        Route::post('/comments', [CommentController::class, 'store']);
        Route::patch('/orders/{orderId}/cancel', [OrderController::class, 'cancel']);
        Route::post('/checkout', [CheckoutController::class, 'process']);
        Route::patch('/profile', [ProfileController::class, 'update']);
    });

    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{reviewId}/comments', [CommentController::class, 'index']);
    Route::get('/payments/{orderId}/status', [PaymentController::class, 'status']);

    Route::middleware(['techshop.user', 'techshop.admin'])->prefix('admin')->group(function (): void {
        Route::patch('/users/{userId}', [AdminCustomerController::class, 'update']);
        Route::patch('/orders/{orderId}/status', [AdminOrderController::class, 'updateStatus']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::patch('/products/{productId}', [AdminProductController::class, 'update']);
        Route::delete('/products/{productId}', [AdminProductController::class, 'destroy']);

        Route::post('/categories', [AdminCategoryController::class, 'storeCategory']);
        Route::patch('/categories/{categoryId}', [AdminCategoryController::class, 'updateCategory']);
        Route::post('/brands', [AdminCategoryController::class, 'storeBrand']);
        Route::patch('/brands/{brandId}', [AdminCategoryController::class, 'updateBrand']);
        Route::delete('/catalog', [AdminCategoryController::class, 'destroy']);
    });
});
