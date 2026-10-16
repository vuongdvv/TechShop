<?php

use App\Http\Controllers\Web\StorefrontController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\VnpayController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/TechShop/home.php'));

Route::prefix('payment/vnpay')->group(function (): void {
    Route::get('/create', [VnpayController::class, 'create'])->name('vnpay.create');
    Route::get('/return', [VnpayController::class, 'return'])->name('vnpay.return');
    Route::get('/ipn', [VnpayController::class, 'ipn'])->name('vnpay.ipn');
});

Route::get('/home.php', [StorefrontController::class, 'home'])->name('store.home');

Route::prefix('auth')->group(function (): void {
    Route::get('/login.php', [StorefrontController::class, 'showLogin'])->name('store.auth.login');
    Route::post('/login.php', [StorefrontController::class, 'login']);
    Route::get('/register.php', [StorefrontController::class, 'showRegister'])->name('store.auth.register');
    Route::post('/register.php', [StorefrontController::class, 'register']);
    Route::get('/logout.php', [StorefrontController::class, 'logout'])->name('store.auth.logout');
});

Route::prefix('product')->group(function (): void {
    Route::get('/list.php', [StorefrontController::class, 'productList'])->name('store.product.list');
    Route::get('/detail.php', [StorefrontController::class, 'productDetail'])->name('store.product.detail');
});

Route::prefix('cart')->group(function (): void {
    Route::get('/index.php', [StorefrontController::class, 'cartIndex'])->name('store.cart.index');
    Route::get('/add.php', [StorefrontController::class, 'cartAdd'])->name('store.cart.add');
    Route::post('/update.php', [StorefrontController::class, 'cartUpdate'])->name('store.cart.update');
    Route::get('/remove.php', [StorefrontController::class, 'cartRemove'])->name('store.cart.remove');
    Route::get('/checkout.php', [StorefrontController::class, 'checkoutShow'])->name('store.cart.checkout.show');
    Route::post('/checkout.php', [StorefrontController::class, 'checkoutForm'])->name('store.cart.checkout.form');
    Route::post('/process_checkout.php', [StorefrontController::class, 'checkoutProcess'])->name('store.cart.checkout.process');
    Route::get('/success.php', [StorefrontController::class, 'checkoutSuccess'])->name('store.cart.success');
});

Route::prefix('orders')->group(function (): void {
    Route::get('/index.php', [StorefrontController::class, 'ordersIndex'])->name('store.orders.index');
    Route::get('/detail.php', [StorefrontController::class, 'ordersDetail'])->name('store.orders.detail');
    Route::post('/cancel_order.php', [StorefrontController::class, 'ordersCancel'])->name('store.orders.cancel');
});

Route::prefix('user')->group(function (): void {
    Route::get('/profile.php', [StorefrontController::class, 'showProfile'])->name('store.user.profile');
    Route::post('/profile.php', [StorefrontController::class, 'updateProfile']);
});

// Explicit hard routes for orders index to avoid rewrite edge cases on some XAMPP setups.
Route::get('/admin/orders/index.php', [AdminController::class, 'orders']);
Route::get('/TechShop/admin/orders/index.php', [AdminController::class, 'orders']);
// Some Apache/PHP combinations may normalize this URL to "ex.php".
Route::get('/ex.php', [AdminController::class, 'orders']);
Route::get('/TechShop/ex.php', [AdminController::class, 'orders']);

Route::prefix('admin')->group(function (): void {
    Route::get('/index.php', fn() => redirect('/admin/dashboard.php'));
    Route::get('/dashboard.php', [AdminController::class, 'dashboard']);

    Route::prefix('products')->group(function (): void {
        Route::get('/product.php', [AdminController::class, 'products']);
        Route::get('/addproduct.php', [AdminController::class, 'addProductForm']);
        Route::post('/addproduct.php', [AdminController::class, 'addProduct']);
        Route::get('/editproduct.php', [AdminController::class, 'editProductForm']);
        Route::post('/editproduct.php', [AdminController::class, 'editProduct']);
        Route::post('/deleteproduct.php', [AdminController::class, 'deleteProduct']);
    });

    Route::prefix('orders')->group(function (): void {
        Route::get('/index.php', [AdminController::class, 'orders']);
        Route::get('/detail.php', [AdminController::class, 'orderDetail']);
        Route::post('/update_status.php', [AdminController::class, 'updateOrderStatus']);
    });

    Route::prefix('customers')->group(function (): void {
        Route::get('/customer.php', [AdminController::class, 'customers']);
    });

    Route::post('/customer/update_user.php', [AdminController::class, 'updateCustomer']);

    Route::prefix('categories')->group(function (): void {
        Route::match(['get', 'post'], '/brand_category.php', [AdminController::class, 'categories']);
        Route::post('/add_brand.php', [AdminController::class, 'addBrand']);
        Route::post('/update_brand_category.php', [AdminController::class, 'updateBrandCategory']);
        Route::get('/delete_brand_category.php', [AdminController::class, 'deleteBrandCategory']);
    });

    Route::get('/admin_auth/logout.php', [AdminController::class, 'logout']);
});

Route::prefix('TechShop')->group(function (): void {
    Route::get('/', fn() => redirect('/TechShop/home.php'));
    Route::get('/home.php', [StorefrontController::class, 'home']);

    Route::prefix('auth')->group(function (): void {
        Route::get('/login.php', [StorefrontController::class, 'showLogin']);
        Route::post('/login.php', [StorefrontController::class, 'login']);
        Route::get('/register.php', [StorefrontController::class, 'showRegister']);
        Route::post('/register.php', [StorefrontController::class, 'register']);
        Route::get('/logout.php', [StorefrontController::class, 'logout']);
    });

    Route::prefix('product')->group(function (): void {
        Route::get('/list.php', [StorefrontController::class, 'productList']);
        Route::get('/detail.php', [StorefrontController::class, 'productDetail']);
    });

    Route::prefix('cart')->group(function (): void {
        Route::get('/index.php', [StorefrontController::class, 'cartIndex']);
        Route::get('/add.php', [StorefrontController::class, 'cartAdd']);
        Route::post('/update.php', [StorefrontController::class, 'cartUpdate']);
        Route::get('/remove.php', [StorefrontController::class, 'cartRemove']);
        Route::get('/checkout.php', [StorefrontController::class, 'checkoutShow']);
        Route::post('/checkout.php', [StorefrontController::class, 'checkoutForm']);
        Route::post('/process_checkout.php', [StorefrontController::class, 'checkoutProcess']);
        Route::get('/success.php', [StorefrontController::class, 'checkoutSuccess']);
    });

    Route::prefix('orders')->group(function (): void {
        Route::get('/index.php', [StorefrontController::class, 'ordersIndex']);
        Route::get('/detail.php', [StorefrontController::class, 'ordersDetail']);
        Route::post('/cancel_order.php', [StorefrontController::class, 'ordersCancel']);
    });

    Route::prefix('user')->group(function (): void {
        Route::get('/profile.php', [StorefrontController::class, 'showProfile']);
        Route::post('/profile.php', [StorefrontController::class, 'updateProfile']);
    });

    Route::prefix('admin')->group(function (): void {
        Route::get('/index.php', fn() => redirect('/TechShop/admin/dashboard.php'));
        Route::get('/dashboard.php', [AdminController::class, 'dashboard']);

        Route::prefix('products')->group(function (): void {
            Route::get('/product.php', [AdminController::class, 'products']);
            Route::get('/addproduct.php', [AdminController::class, 'addProductForm']);
            Route::post('/addproduct.php', [AdminController::class, 'addProduct']);
            Route::get('/editproduct.php', [AdminController::class, 'editProductForm']);
            Route::post('/editproduct.php', [AdminController::class, 'editProduct']);
            Route::post('/deleteproduct.php', [AdminController::class, 'deleteProduct']);
        });

        Route::prefix('orders')->group(function (): void {
            Route::get('/index.php', [AdminController::class, 'orders']);
            Route::get('/detail.php', [AdminController::class, 'orderDetail']);
            Route::post('/update_status.php', [AdminController::class, 'updateOrderStatus']);
        });

        Route::prefix('customers')->group(function (): void {
            Route::get('/customer.php', [AdminController::class, 'customers']);
        });

        Route::post('/customer/update_user.php', [AdminController::class, 'updateCustomer']);

        Route::prefix('categories')->group(function (): void {
            Route::match(['get', 'post'], '/brand_category.php', [AdminController::class, 'categories']);
            Route::post('/add_brand.php', [AdminController::class, 'addBrand']);
            Route::post('/update_brand_category.php', [AdminController::class, 'updateBrandCategory']);
            Route::get('/delete_brand_category.php', [AdminController::class, 'deleteBrandCategory']);
        });

        Route::get('/admin_auth/logout.php', [AdminController::class, 'logout']);
    });
});
