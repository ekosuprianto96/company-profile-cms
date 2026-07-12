<?php

use App\Http\Controllers\Api\Mobile\AppContentController;
use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\MobileBudgetOptionController;
use App\Http\Controllers\Api\Mobile\SearchController;
use App\Http\Controllers\Api\Mobile\MapsController;
use App\Http\Controllers\Api\Mobile\MobileServiceController;
use App\Http\Controllers\Api\Mobile\MobileServiceRequestController;
use App\Http\Controllers\Api\Mobile\MobileInvoiceController;
use App\Http\Controllers\Api\Mobile\MobileProductOrderController;
use App\Http\Controllers\Api\Mobile\VoucherController;
use App\Http\Controllers\Api\Mobile\ChatController;
use App\Http\Controllers\Api\Mobile\PushTokenController;
use App\Http\Controllers\Api\Mobile\NotificationController;
use App\Http\Controllers\Api\Mobile\MidtransController;
use App\Http\Controllers\Api\Mobile\InspireController;
use App\Http\Controllers\Api\Mobile\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('v1/mobile/services', [MobileServiceController::class, 'index']);
Route::get('v1/mobile/services/event-options', [MobileServiceController::class, 'eventOptions']);
Route::get('v1/mobile/budget-options', [MobileBudgetOptionController::class, 'index']);
Route::get('v1/mobile/search/popular', [SearchController::class, 'popular']);
Route::post('v1/mobile/maps/autocomplete', [MapsController::class, 'autocomplete']);
Route::post('v1/mobile/maps/resolve', [MapsController::class, 'resolve']);
Route::post('v1/mobile/maps/reverse-geocode', [MapsController::class, 'reverseGeocode']);
Route::get('v1/mobile/inspires', [InspireController::class, 'index']);
Route::get('v1/mobile/inspires/{slug}', [InspireController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/blogs', [BlogController::class, 'index']);
Route::get('v1/mobile/blogs/{slug}', [BlogController::class, 'show'])->where('slug', '[A-Za-z0-9\-]+');
Route::get('v1/mobile/app-content', [AppContentController::class, 'index']);
Route::get('v1/mobile/service-requests/meta', [MobileServiceRequestController::class, 'meta']);
Route::middleware('auth:sanctum')->post('v1/mobile/service-requests/upload-photo', [MobileServiceRequestController::class, 'uploadIssuePhoto']);
Route::post('v1/mobile/midtrans/notification', [MidtransController::class, 'notification']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('v1/mobile/push-tokens', [PushTokenController::class, 'store']);
    Route::delete('v1/mobile/push-tokens/current', [PushTokenController::class, 'destroyCurrent']);

    Route::prefix('v1/mobile/service-requests')->group(function () {
        Route::get('/', [MobileServiceRequestController::class, 'index'])->name('service-requests.index');
        Route::get('/{id}', [MobileServiceRequestController::class, 'show'])->whereNumber('id')->name('service-requests.show');
        Route::get('/{id}/invoice', [MobileInvoiceController::class, 'serviceRequest'])->whereNumber('id')->name('service-requests.invoice');
        Route::post('/', [MobileServiceRequestController::class, 'storeDraft'])->name('service-requests.store');
        Route::post('/draft', [MobileServiceRequestController::class, 'storeDraft'])->name('service-requests.draft');
        Route::patch('/{id}/payment-method', [MobileServiceRequestController::class, 'updatePaymentMethod'])->name('service-requests.payment-method');
        Route::patch('/{id}/cancel', [MobileServiceRequestController::class, 'cancel'])->whereNumber('id')->name('service-requests.cancel');
    });

    Route::prefix('v1/mobile/vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::get('/available', [VoucherController::class, 'available'])->name('vouchers.available');
        Route::post('/preview', [VoucherController::class, 'preview'])->name('vouchers.preview');
    });

    Route::get('v1/mobile/product-orders/{orderNumber}/invoice', [MobileInvoiceController::class, 'productOrder'])->name('product-orders.invoice');
    Route::patch('v1/mobile/product-orders/{orderNumber}/cancel', [MobileProductOrderController::class, 'cancel'])->name('product-orders.cancel');

    Route::prefix('v1/mobile/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/read-all', [NotificationController::class, 'markAllRead']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::patch('/{id}/read', [NotificationController::class, 'markRead']);
    });

    Route::prefix('v1/mobile/chats')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::post('/', [ChatController::class, 'store']);
        Route::get('/{id}', [ChatController::class, 'show'])->whereNumber('id');
        Route::get('/{id}/messages', [ChatController::class, 'messages'])->whereNumber('id');
        Route::post('/{id}/messages', [ChatController::class, 'storeMessage'])->whereNumber('id');
        Route::patch('/{id}/read', [ChatController::class, 'markRead'])->whereNumber('id');
    });
});

Route::prefix('v1/mobile/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/password/otp', [AuthController::class, 'sendPasswordOtp']);
        Route::post('/password/verify', [AuthController::class, 'verifyPasswordOtp']);
        Route::post('/password', [AuthController::class, 'updatePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
