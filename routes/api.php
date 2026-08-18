<?php

use App\Http\Controllers\Api\Mobile\AppContentController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\SearchController;
use App\Http\Controllers\Api\Mobile\MapsController;
use App\Http\Controllers\Api\Mobile\MobileServiceController;
use App\Http\Controllers\Api\Mobile\MobileServiceRequestController;
use App\Http\Controllers\Api\Mobile\MobileInvoiceController;
use App\Http\Controllers\Api\Mobile\MobileProductOrderController;
use App\Http\Controllers\Api\Mobile\PromotionController as MobilePromotionController;
use App\Http\Controllers\Api\Mobile\VoucherController;
use App\Http\Controllers\Api\Mobile\ProductController as ProductApiController;
use App\Http\Controllers\Api\Mobile\ProductReviewController;
use App\Http\Controllers\Api\Mobile\ChatController;
use App\Http\Controllers\Api\Mobile\ProposalController;
use App\Http\Controllers\Api\Mobile\PushTokenController;
use App\Http\Controllers\Api\Mobile\NotificationController;
use App\Http\Controllers\Api\Mobile\MidtransController;
use App\Http\Controllers\Api\Mobile\HomeSectionController as MobileHomeSectionController;
use App\Http\Controllers\Api\Mobile\InspireController;
use App\Http\Controllers\Api\Mobile\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('v1/mobile/services', [MobileServiceController::class, 'index']);
Route::get('v1/mobile/services/{slug}/form', [MobileServiceController::class, 'formSchema'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/search/popular', [SearchController::class, 'popular']);
Route::post('v1/mobile/maps/autocomplete', [MapsController::class, 'autocomplete']);
Route::post('v1/mobile/maps/resolve', [MapsController::class, 'resolve']);
Route::post('v1/mobile/maps/reverse-geocode', [MapsController::class, 'reverseGeocode']);
Route::get('v1/mobile/inspires', [InspireController::class, 'index']);
Route::get('v1/mobile/inspires/{slug}', [InspireController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/blogs', [BlogController::class, 'index']);
Route::get('v1/mobile/blogs/{slug}', [BlogController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/app-content', [AppContentController::class, 'index']);
Route::get('v1/mobile/home-sections', [MobileHomeSectionController::class, 'index']);
Route::get('v1/mobile/products', [ProductApiController::class, 'index']);
Route::get('v1/mobile/product-categories', [ProductApiController::class, 'categories']);
Route::get('v1/mobile/products/{slug}', [ProductApiController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/products/{slug}/reviews', [ProductReviewController::class, 'index'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/service-requests/meta', [MobileServiceRequestController::class, 'meta']);
Route::middleware(['auth:sanctum', 'mobile.active'])->post('v1/mobile/service-requests/upload-photo', [MobileServiceRequestController::class, 'uploadIssuePhoto']);
Route::post('v1/mobile/midtrans/notification', [MidtransController::class, 'notification']);

// Promosi (banner beranda + halaman detail) — publik, tidak perlu login.
Route::get('v1/mobile/promotions', [MobilePromotionController::class, 'index'])->name('promotions.index');
Route::get('v1/mobile/promotions/{slug}', [MobilePromotionController::class, 'show'])->name('promotions.show');

Route::middleware(['auth:sanctum', 'mobile.active'])->group(function () {
    Route::post('v1/mobile/push-tokens', [PushTokenController::class, 'store']);
    Route::delete('v1/mobile/push-tokens/current', [PushTokenController::class, 'destroyCurrent']);

    Route::prefix('v1/mobile/addresses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Mobile\MobileUserAddressController::class, 'index'])->name('addresses.index');
        Route::post('/', [\App\Http\Controllers\Api\Mobile\MobileUserAddressController::class, 'store'])->name('addresses.store');
        Route::put('/{id}', [\App\Http\Controllers\Api\Mobile\MobileUserAddressController::class, 'update'])->whereNumber('id')->name('addresses.update');
        Route::patch('/{id}/primary', [\App\Http\Controllers\Api\Mobile\MobileUserAddressController::class, 'setPrimary'])->whereNumber('id')->name('addresses.primary');
        Route::delete('/{id}', [\App\Http\Controllers\Api\Mobile\MobileUserAddressController::class, 'destroy'])->whereNumber('id')->name('addresses.destroy');
    });

    Route::prefix('v1/mobile/service-requests')->group(function () {
        Route::get('/', [MobileServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/{id}', [MobileServiceRequestController::class, 'show'])->whereNumber('id')->name('service-requests.show');
        Route::get('/{id}/invoice', [MobileInvoiceController::class, 'serviceRequest'])->whereNumber('id')->name('service-requests.invoice');
        Route::post('/', [MobileServiceRequestController::class, 'storeDraft'])->name('service-requests.store');
        Route::post('/draft', [MobileServiceRequestController::class, 'storeDraft'])->name('service-requests.draft');
        Route::patch('/{id}/payment-method', [MobileServiceRequestController::class, 'updatePaymentMethod'])->name('service-requests.payment-method');
        Route::post('/{id}/payment-proof', [MobileServiceRequestController::class, 'uploadPaymentProof'])->whereNumber('id')->name('service-requests.payment-proof');
        Route::patch('/{id}/cancel', [MobileServiceRequestController::class, 'cancel'])->whereNumber('id')->name('service-requests.cancel');
    });

    Route::prefix('v1/mobile/proposals')->group(function () {
        Route::get('/', [ProposalController::class, 'index'])->name('proposals.index');
        Route::post('/', [ProposalController::class, 'store'])->name('proposals.store');
        Route::post('/upload', [ProposalController::class, 'upload'])->name('proposals.upload');
        Route::get('/{id}', [ProposalController::class, 'show'])->whereNumber('id')->name('proposals.show');
    });

    Route::prefix('v1/mobile/vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/available', [VoucherController::class, 'available'])->name('vouchers.available');
        Route::post('/preview', [VoucherController::class, 'preview'])->name('vouchers.preview');
        Route::get('/{id}', [VoucherController::class, 'show'])->whereNumber('id')->name('vouchers.show');
        Route::post('/{id}/claim', [VoucherController::class, 'claim'])->whereNumber('id')->name('vouchers.claim');
    });

    Route::get('v1/mobile/shipping/couriers', [MobileProductOrderController::class, 'couriers'])->name('shipping.couriers');
    Route::post('v1/mobile/product-orders', [MobileProductOrderController::class, 'checkout'])->name('product-orders.checkout');
    Route::get('v1/mobile/product-orders', [MobileProductOrderController::class, 'index'])->name('product-orders.index');
    Route::get('v1/mobile/product-orders/{orderNumber}/invoice', [MobileInvoiceController::class, 'productOrder'])->name('product-orders.invoice');
    Route::patch('v1/mobile/product-orders/{orderNumber}/payment-method', [MobileProductOrderController::class, 'selectPaymentMethod'])->name('product-orders.payment-method');
    Route::post('v1/mobile/product-orders/{orderNumber}/payment-proof', [MobileProductOrderController::class, 'uploadPaymentProof'])->name('product-orders.payment-proof');
    Route::patch('v1/mobile/product-orders/{orderNumber}/cancel', [MobileProductOrderController::class, 'cancel'])->name('product-orders.cancel');
    Route::get('v1/mobile/product-orders/{orderNumber}', [MobileProductOrderController::class, 'show'])->name('product-orders.show');
    Route::post('v1/mobile/product-orders/{orderNumber}/reviews', [ProductReviewController::class, 'store'])->name('product-orders.reviews.store');

    Route::prefix('v1/mobile/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/read-all', [NotificationController::class, 'markAllRead']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::patch('/{id}/read', [NotificationController::class, 'markRead']);
    });

    Route::prefix('v1/mobile/chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::get('/unread-count', [ChatController::class, 'unreadCount']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{id}', [ChatController::class, 'show'])->whereNumber('id');
        Route::get('/{id}/messages', [ChatController::class, 'messages'])->whereNumber('id');
        Route::post('/{id}/messages', [ChatController::class, 'storeMessage'])->whereNumber('id');
        Route::post('/{id}/typing', [ChatController::class, 'typing'])->whereNumber('id');
        Route::patch('/{id}/read', [ChatController::class, 'markRead'])->whereNumber('id');
    });
});

/*
|--------------------------------------------------------------------------
| Aplikasi ADMIN (mobile) — login pakai akun users + credential key + OTP email.
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admin')->group(function () {
    // Rate-limit auth publik: cegah brute-force login/OTP & flooding email OTP.
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('auth/login', [\App\Http\Controllers\Api\Admin\AuthController::class, 'login']);
        Route::post('auth/otp/verify', [\App\Http\Controllers\Api\Admin\AuthController::class, 'verifyOtp']);
        Route::post('auth/otp/resend', [\App\Http\Controllers\Api\Admin\AuthController::class, 'resendOtp']);
    });

    // Invoice PDF (token via query supaya bisa dibuka in-app browser). Divalidasi manual.
    Route::get('invoice/{type}/{id}', [\App\Http\Controllers\Api\Admin\InvoiceController::class, 'show'])->whereNumber('id')->where('type', 'service|product');

    Route::middleware(['auth:sanctum', 'admin.access'])->group(function () {
        Route::get('auth/me', [\App\Http\Controllers\Api\Admin\AuthController::class, 'me']);
        Route::post('auth/logout', [\App\Http\Controllers\Api\Admin\AuthController::class, 'logout']);

        Route::get('dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('analytics', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'analytics']);

        // Orders
        Route::get('orders', [\App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
        Route::get('orders/service/{id}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'showService'])->whereNumber('id');
        Route::get('orders/product/{id}', [\App\Http\Controllers\Api\Admin\OrderController::class, 'showProduct'])->whereNumber('id');
        Route::patch('orders/service/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateServiceStatus'])->whereNumber('id');
        Route::post('orders/service/{id}/verify-payment', [\App\Http\Controllers\Api\Admin\OrderController::class, 'verifyServicePayment'])->whereNumber('id');
        Route::post('orders/service/{id}/steps/complete', [\App\Http\Controllers\Api\Admin\OrderController::class, 'completeServiceStep'])->whereNumber('id');
        Route::post('orders/service/{id}/steps/reopen', [\App\Http\Controllers\Api\Admin\OrderController::class, 'reopenServiceStep'])->whereNumber('id');
        Route::patch('orders/product/{id}/status', [\App\Http\Controllers\Api\Admin\OrderController::class, 'updateProductStatus'])->whereNumber('id');

        // Payments
        Route::get('payments/pending', [\App\Http\Controllers\Api\Admin\PaymentController::class, 'pending']);

        // Chat
        Route::get('chats', [\App\Http\Controllers\Api\Admin\ChatController::class, 'index']);
        Route::post('chats/start', [\App\Http\Controllers\Api\Admin\ChatController::class, 'start']);
        Route::get('chats/{id}', [\App\Http\Controllers\Api\Admin\ChatController::class, 'show'])->whereNumber('id');
        Route::post('chats/{id}/messages', [\App\Http\Controllers\Api\Admin\ChatController::class, 'send'])->whereNumber('id');
        Route::patch('chats/{id}/read', [\App\Http\Controllers\Api\Admin\ChatController::class, 'markRead'])->whereNumber('id');

        // Services (kelola cepat + stop pengajuan)
        Route::get('services', [\App\Http\Controllers\Api\Admin\ServiceController::class, 'index']);
        Route::get('services/{id}', [\App\Http\Controllers\Api\Admin\ServiceController::class, 'show'])->whereNumber('id');
        Route::patch('services/{id}', [\App\Http\Controllers\Api\Admin\ServiceController::class, 'update'])->whereNumber('id');

        // Customers
        Route::get('customers/{id}', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'show'])->whereNumber('id');
        Route::post('customers/{id}/ban', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'ban'])->whereNumber('id');
        Route::post('customers/{id}/unban', [\App\Http\Controllers\Api\Admin\CustomerController::class, 'unban'])->whereNumber('id');
    });
});

Route::prefix('v1/mobile/auth')->group(function () {
    // Rate-limit auth publik: cegah brute-force & flooding OTP/email.
    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/register/check', [AuthController::class, 'checkRegistration']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/otp/send', [AuthController::class, 'sendOtp']);
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
    });

    Route::middleware(['auth:sanctum', 'mobile.active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/password/otp', [AuthController::class, 'sendPasswordOtp']);
        Route::post('/password/verify', [AuthController::class, 'verifyPasswordOtp']);
        Route::post('/password', [AuthController::class, 'updatePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
