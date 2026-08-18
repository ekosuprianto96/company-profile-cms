<?php

use App\Models\User;
use App\Models\Visitor;
use App\Services\BlogService;
use App\Charts\AnalitycVisitorChart;
use App\Services\EmailMessageService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RobotsController;
use App\Http\Controllers\Admin\VisitorController;
use App\Http\Controllers\Admin\CKEditorController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Blogs\BlogController;
use App\Http\Controllers\Admin\FileUploadController;
use App\Http\Controllers\Admin\Menus\MenuController;
use App\Http\Controllers\Admin\Pages\PageController;
use App\Http\Controllers\Admin\Roles\RoleController;
use App\Http\Controllers\Admin\SectionPageController;
use App\Http\Controllers\Admin\System\SystemMonitorController;
use App\Http\Controllers\Admin\Groups\GroupController;
use App\Http\Controllers\Admin\Themes\ThemeController;
use App\Http\Controllers\Admin\Auth\PenggunaController;
use App\Http\Controllers\Admin\InformasiPageController;
use App\Http\Controllers\Admin\Mobile\MobileController;
use App\Http\Controllers\Admin\Mobile\ChatController as MobileChatController;
use App\Http\Controllers\Admin\Mobile\InspireController as MobileInspireController;
use App\Http\Controllers\Admin\Mobile\MobileContentController;
use App\Http\Controllers\Admin\Mobile\MobileSupportContactController;
use App\Http\Controllers\Admin\Mobile\VoucherController;
use App\Http\Controllers\Admin\Mobile\ProductController;
use App\Http\Controllers\Admin\Mobile\ProductCategoryController;
use App\Http\Controllers\Admin\Mobile\ShippingCourierController;
use App\Http\Controllers\Admin\Mobile\ProductOrderController;
use App\Http\Controllers\Admin\Mobile\ReviewController;
use App\Http\Controllers\Admin\Mobile\PromotionController;
use App\Http\Controllers\Admin\Mobile\CategoryController;
use App\Http\Controllers\Admin\Mobile\HomeSectionController;
use App\Http\Controllers\Admin\Mobile\FormController;
use App\Http\Controllers\Admin\Mobile\ProposalController;
use App\Http\Controllers\Admin\Mobile\MobileServiceRequestController;
use App\Http\Controllers\Admin\Mobile\MobileServiceController;
use App\Http\Controllers\Admin\Widget\WidgetController;
use App\Http\Controllers\Admin\Banners\BannerController;
use App\Http\Controllers\Admin\Blogs\KategoriController;
use App\Http\Controllers\Admin\Modules\ModuleController;
use App\Http\Controllers\Admin\Pages\HomePageController;
use App\Http\Controllers\Admin\Gallery\GalleryController;
use App\Http\Controllers\Admin\Layanan\LayananController;
use App\Http\Controllers\Admin\Profile\ProfileController;
use App\Http\Controllers\Admin\Sitemap\SitemapController;
use App\Http\Controllers\Admin\Packages\PackageController;
use App\Http\Controllers\Admin\Roles\PermissionController;
use App\Http\Controllers\Admin\Auth\AuthenticateController;
use App\Http\Controllers\Admin\Editor\EditorPageController;
use App\Http\Controllers\Admin\Email\ContactEmailController;
use App\Http\Controllers\Admin\Email\EmailManagementController;
use App\Http\Controllers\Admin\Rekomendasi\RekomendasiKavlingController;
use App\Http\Controllers\Admin\SocialMedia\SocialMediaController;

Route::middleware(['guest'])->name('auth.')->group(function () {
    Route::get('/login', [AuthenticateController::class, 'login'])->name('login');
    Route::post('/check', [AuthenticateController::class, 'check'])->name('check');
});

Route::middleware(['auth'])->group(function () {
    Route::get('', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('dashboard', function (
        AnalitycVisitorChart $chart,
        BlogService $blog,
        EmailMessageService $message,
        \App\Services\System\ServerMetricsService $serverMetrics
    ) {
        $countPost = $blog->getCount();
        $userCount = User::count();
        $visitorCount = Visitor::count();
        $unread = $message->getAllMessage(function ($query) {
            return $query->where('is_read', 0);
        });
        $unreadCount = $unread->count();
        // Tampilkan maksimal 8 di dashboard; sisanya lewat menu Pesan Email.
        $unreadMessages = $unread->take(8);

        return view('admin.pages.dashboard.index', [
            'chart' => $chart->build(),
            'countPost' => $countPost,
            'userCount' => $userCount,
            'unreadMessages' => $unreadMessages,
            'unreadCount' => $unreadCount,
            'visitorCount' => $visitorCount,
            'serverMetrics' => $serverMetrics->all(),
        ]);
    })->name('dashboard');

    // Monitoring sistem: job/queue, cron schedule, kondisi server. Khusus superadmin.
    Route::prefix('system/')->name('system.')->middleware('superadmin')
        ->group(function () {
            Route::get('jobs', [SystemMonitorController::class, 'jobs'])->name('jobs');
            Route::get('schedule', [SystemMonitorController::class, 'schedule'])->name('schedule');
            Route::get('metrics', [SystemMonitorController::class, 'serverMetrics'])->name('metrics');

            Route::post('jobs/{uuid}/retry', [SystemMonitorController::class, 'retry'])->name('jobs.retry');
            Route::post('jobs/retry-all', [SystemMonitorController::class, 'retryAll'])->name('jobs.retry_all');
            Route::delete('jobs/{uuid}/forget', [SystemMonitorController::class, 'forget'])->name('jobs.forget');
            Route::delete('jobs/flush', [SystemMonitorController::class, 'flush'])->name('jobs.flush');
            Route::delete('pending/{id}/stop', [SystemMonitorController::class, 'stopPending'])->name('pending.stop');
            Route::delete('pending/purge', [SystemMonitorController::class, 'purgePending'])->name('pending.purge');
            Route::post('schedule/run', [SystemMonitorController::class, 'runTask'])->name('schedule.run');
        });

    // Panduan / log book admin (PDF)
    Route::get('panduan/preview', [\App\Http\Controllers\Admin\AdminGuideController::class, 'preview'])->name('guide.preview');
    Route::get('panduan/download', [\App\Http\Controllers\Admin\AdminGuideController::class, 'download'])->name('guide.download');

    Route::prefix('modules/')->name('modules.')->group(function () {
        Route::get('', [ModuleController::class, 'index'])->name('index')->middleware('permission:module:show');
        Route::get('/forms', [ModuleController::class, 'forms'])->name('forms');
        Route::post('/store', [ModuleController::class, 'storeModule'])->name('store')->middleware('permission:module:create');
        Route::post('/update/{id_module}', [ModuleController::class, 'updateModule'])->name('update')->middleware('permission:module:update');
        Route::get('/data', [ModuleController::class, 'data'])->name('data');
        Route::post('/destroy', [ModuleController::class, 'destroy'])->name('destroy')->middleware('permission:module:destroy');
    });

    Route::prefix('pages/')->name('pages.')->group(function () {
        // home
        Route::get('{id}', [PageController::class, 'index'])->name('index');
        Route::post('{id}/{type}', [PageController::class, 'storePage'])->name('store');
    });

    Route::prefix('menus/')->name('menus.')->group(function () {
        Route::get('', [MenuController::class, 'index'])->name('index')->middleware('permission:menu:show');
        Route::get('/forms', [MenuController::class, 'forms'])->name('forms');
        Route::post('/store', [MenuController::class, 'storeMenu'])->name('store')->middleware('permission:menu:create');
        Route::post('/update/{id_menu}', [MenuController::class, 'updateMenu'])->name('update')->middleware('permission:menu:update');
        Route::get('/data', [MenuController::class, 'data'])->name('data');
        Route::post('/destroy', [MenuController::class, 'destroy'])->name('destroy')->middleware('permission:module:destroy');
        Route::post('/attach-role', [MenuController::class, 'attachRoleMenu'])->name('attach');
    });

    Route::prefix('banners/')->name('banners.')->group(function () {
        Route::get('', [BannerController::class, 'index'])->name('index')->middleware('permission:banner:show');
        Route::get('/forms', [BannerController::class, 'forms'])->name('forms');
        Route::post('/store', [BannerController::class, 'storeBanner'])->name('store')->middleware('permission:banner:store');
        Route::post('/update/{id}', [BannerController::class, 'updateBanner'])->name('update')->middleware('permission:banner:update');
        Route::get('/data', [BannerController::class, 'data'])->name('data');
        Route::post('/destroy', [BannerController::class, 'destroy'])->name('destroy')->middleware('permission:banner:destroy');
    });

    Route::prefix('services/')->name('services.')->group(function () {
        Route::get('', [LayananController::class, 'index'])->name('index')->middleware('permission:service:show');
        Route::get('/forms', [LayananController::class, 'forms'])->name('forms');
        Route::post('/store', [LayananController::class, 'storeService'])->name('store')->middleware('permission:service:store');
        Route::post('/update/{id}', [LayananController::class, 'updateService'])->name('update')->middleware('permission:service:update');
        Route::get('/data', [LayananController::class, 'data'])->name('data');
        Route::post('/destroy', [LayananController::class, 'destroy'])->name('destroy')->middleware('permission:service:destroy');
    });

    Route::prefix('blogs/')->name('blogs.')->group(function () {
        Route::get('', [BlogController::class, 'index'])->name('index')->middleware('permission:blog:show');
        Route::get('/create', [BlogController::class, 'createBlog'])->name('create')->middleware('permission:blog:create');
        Route::post('/store', [BlogController::class, 'storeBlog'])->name('store')->middleware('permission:blog:store');
        Route::get('/edit/{slug}', [BlogController::class, 'editBlog'])->name('edit')->middleware('permission:blog:edit');
        Route::post('/update/{slug}', [BlogController::class, 'updateBlog'])->name('update')->middleware('permission:blog:update');
        Route::get('/data', [BlogController::class, 'data'])->name('data');
        Route::post('/destroy', [BlogController::class, 'destroy'])->name('destroy')->middleware('permission:blog:destroy');

        // Kategori
        Route::prefix('kategori/')->name('kategori.')->group(function () {
            Route::get('', [KategoriController::class, 'index'])->name('index')->middleware('permission:blog-kategori:show');
            Route::get('/forms', [KategoriController::class, 'forms'])->name('forms');
            Route::post('/store', [KategoriController::class, 'storeKategori'])->name('store')->middleware('permission:blog-kategori:store');
            Route::post('/update/{id}', [KategoriController::class, 'updateKategori'])->name('update')->middleware('permission:blog-kategori:update');
            Route::get('/data', [KategoriController::class, 'data'])->name('data');
            Route::post('/destroy', [KategoriController::class, 'destroy'])->name('destroy')->middleware('permission:blog-kategori:destroy');
        });
    });

    Route::prefix('galleries/')->name('galleries.')->group(function () {
        Route::get('', [GalleryController::class, 'index'])->name('index')->middleware('permission:gallery:show');
        Route::get('/forms', [GalleryController::class, 'forms'])->name('forms');
        Route::post('/store', [GalleryController::class, 'storeGallery'])->name('store')->middleware('permission:gallery:store');
        Route::post('/update/{id_menu}', [GalleryController::class, 'updateGallery'])->name('update')->middleware('permission:gallery:update');
        Route::get('/data', [GalleryController::class, 'data'])->name('data');
        Route::post('/destroy', [GalleryController::class, 'destroy'])->name('destroy')->middleware('permission:gallery:destroy');
    });

    Route::prefix('groups/')->name('groups.')->group(function () {
        Route::get('', [GroupController::class, 'index'])->name('index')->middleware('permission:group:show');
        Route::get('/forms', [GroupController::class, 'forms'])->name('forms');
        Route::post('/store', [GroupController::class, 'storeGroup'])->name('store')->middleware('permission:group:create');
        Route::post('/update/{id_group}', [GroupController::class, 'updateGroup'])->name('update')->middleware('permission:group:create');
        Route::get('/data', [GroupController::class, 'data'])->name('data');
        Route::post('/destroy', [GroupController::class, 'destroy'])->name('destroy')->middleware('permission:group:destroy');
    });

    Route::prefix('roles/')->name('roles.')->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('index')->middleware('permission:role:show');
        Route::get('/forms', [RoleController::class, 'forms'])->name('forms');
        Route::get('/setting/{id_role}', [RoleController::class, 'settings'])->name('setting');
        Route::post('/store', [RoleController::class, 'storeRole'])->name('store')->middleware('permission:role:create');
        Route::post('/update/{id_menu}', [RoleController::class, 'updateRole'])->name('update')->middleware('permission:role:update');
        Route::get('/data', [RoleController::class, 'data'])->name('data');
        Route::post('/destroy', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:role:destroy');
        Route::post('/attach-permission', [RoleController::class, 'attachPermission'])->name('attach');
    });

    Route::prefix('permissions/')->name('permissions.')->group(function () {
        Route::get('', [PermissionController::class, 'index'])->name('index')->middleware('permission:permission:show');
        Route::get('/forms', [PermissionController::class, 'forms'])->name('forms');
        Route::post('/store', [PermissionController::class, 'storePermission'])->name('store')->middleware('permission:permission:create');
        Route::post('/update/{id}', [PermissionController::class, 'updatePermission'])->name('update')->middleware('permission:permission:update');
        Route::get('/data', [PermissionController::class, 'data'])->name('data');
        Route::post('/destroy', [PermissionController::class, 'destroy'])->name('destroy')->middleware('permission:permission:destroy');
    });

    Route::prefix('pengguna/')->name('pengguna.')->group(function () {
        Route::get('', [PenggunaController::class, 'index'])->name('index')->middleware('permission:pengguna:show');
        Route::get('/create', [PenggunaController::class, 'create'])->name('create')->middleware('permission:pengguna:create');
        Route::get('/edit/{id}', [PenggunaController::class, 'edit'])->name('edit')->middleware('permission:pengguna:edit');
        Route::post('/store', [PenggunaController::class, 'storePengguna'])->name('store')->middleware('permission:pengguna:store');
        Route::post('/update/{user_id}', [PenggunaController::class, 'updatePengguna'])->name('update')->middleware('permission:pengguna:update');
        Route::post('/{id}/mobile-access', [PenggunaController::class, 'toggleMobileAccess'])->name('mobile_access')->middleware('permission:pengguna:update');
        Route::post('/{id}/regenerate-credential', [PenggunaController::class, 'regenerateCredential'])->name('regenerate_credential')->middleware('permission:pengguna:update');
        Route::get('/data', [PenggunaController::class, 'data'])->name('data');
        Route::post('/destroy', [PenggunaController::class, 'destroy'])->name('destroy')->middleware('permission:pengguna:destroy');
    });

    // profile
    Route::prefix('profile/')->name('profile.')->group(function () {
        Route::get('', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
        Route::post('/generate-credential', [ProfileController::class, 'generateCredential'])->name('generate-credential');
    });

    Route::prefix('page-editor')->name('page-editor.')->group(function () {
        Route::get('', [EditorPageController::class, 'index'])->name('index');
    });

    // informasi
    Route::prefix('informasi/')->name('informasi.')->group(function () {
        Route::get('', [InformasiPageController::class, 'index'])->name('index');
        Route::get('/forms', [InformasiPageController::class, 'forms'])->name('forms');
        Route::post('/update/{id}', [InformasiPageController::class, 'update'])->name('update');
    });

    // sections
    Route::get('sections/forms', [SectionPageController::class, 'forms'])->name('sections.forms');
    Route::post('sections/{page}/{section}/update', [SectionPageController::class, 'updateSection'])->name('sections.update');

    // sitemap
    Route::prefix('sitemap/')->name('sitemap.')->group(function () {
        Route::get('', [SitemapController::class, 'index'])->name('index');
        Route::post('generate', [SitemapController::class, 'generate'])->name('generate');
        Route::get('preview', [SitemapController::class, 'preview'])->name('preview');
    });

    // Robots.txt
    Route::prefix('robots/')->name('robots.')->group(function () {
        Route::get('', [RobotsController::class, 'index'])->name('index');
        Route::post('update', [RobotsController::class, 'update'])->name('update');
    });

    // ck editor
    Route::post('ckeditor/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');
    Route::post('ckeditor/cleanup', [CKEditorController::class, 'cleanup'])->name('ckeditor.cleanup');

    // settings
    Route::prefix('settings/')->name('settings.')->group(function () {
        Route::get('', [SettingsController::class, 'index'])->name('index');
        Route::post('update', [SettingsController::class, 'update'])->name('update');
    });

    Route::prefix('mobile/')->name('mobile.')->group(function () {
        Route::get('', [MobileController::class, 'index'])->name('index');
        Route::get('users', [MobileController::class, 'users'])->name('users');
        Route::get('users/data', [MobileController::class, 'usersData'])->name('users.data');
        Route::get('users/{id}', [MobileController::class, 'showUser'])->whereNumber('id')->name('users.show');
        Route::post('users/{id}/toggle-status', [MobileController::class, 'toggleUserStatus'])->name('users.toggle_status');
        Route::post('users/{id}/revoke-tokens', [MobileController::class, 'revokeUserTokens'])->name('users.revoke_tokens');
        Route::post('users/{id}/ban', [MobileController::class, 'banUser'])->whereNumber('id')->name('users.ban');
        Route::post('users/{id}/unban', [MobileController::class, 'unbanUser'])->whereNumber('id')->name('users.unban');
        Route::get('otp-logs', [MobileController::class, 'otpLogs'])->name('otp_logs');
        Route::get('otp-logs/data', [MobileController::class, 'otpLogsData'])->name('otp_logs.data');
        Route::get('services', [MobileServiceController::class, 'index'])->name('services');
        Route::get('services/data', [MobileServiceController::class, 'data'])->name('services.data');
        Route::get('services/forms', [MobileServiceController::class, 'forms'])->name('services.forms');
        Route::get('services/create', [MobileServiceController::class, 'create'])->name('services.create');
        Route::get('services/edit/{id}', [MobileServiceController::class, 'edit'])->whereNumber('id')->name('services.edit');
        Route::post('services/store', [MobileServiceController::class, 'store'])->name('services.store');
        Route::post('services/update/{id}', [MobileServiceController::class, 'update'])->name('services.update');
        Route::post('services/destroy', [MobileServiceController::class, 'destroy'])->name('services.destroy');

        // App content (Tentang / Syarat & Ketentuan)
        Route::get('contents', [MobileContentController::class, 'index'])->name('contents');
        Route::post('contents/update', [MobileContentController::class, 'update'])->name('contents.update');

        // Kontak Bantuan & Dukungan (CRUD)
        Route::get('support-contacts', [MobileSupportContactController::class, 'index'])->name('support_contacts');
        Route::get('support-contacts/data', [MobileSupportContactController::class, 'data'])->name('support_contacts.data');
        Route::get('support-contacts/forms', [MobileSupportContactController::class, 'forms'])->name('support_contacts.forms');
        Route::post('support-contacts/store', [MobileSupportContactController::class, 'store'])->name('support_contacts.store');
        Route::post('support-contacts/update/{id}', [MobileSupportContactController::class, 'update'])->name('support_contacts.update');
        Route::post('support-contacts/destroy', [MobileSupportContactController::class, 'destroy'])->name('support_contacts.destroy');

        // Voucher (CRUD) — permission-gated
        Route::get('vouchers', [VoucherController::class, 'index'])->name('vouchers')->middleware('permission:voucher:show');
        Route::get('vouchers/data', [VoucherController::class, 'data'])->name('vouchers.data')->middleware('permission:voucher:show');
        Route::get('vouchers/forms', [VoucherController::class, 'forms'])->name('vouchers.forms')->middleware('permission:voucher:show');
        Route::post('vouchers/store', [VoucherController::class, 'store'])->name('vouchers.store')->middleware('permission:voucher:create');
        Route::post('vouchers/update/{id}', [VoucherController::class, 'update'])->whereNumber('id')->name('vouchers.update')->middleware('permission:voucher:update');
        Route::post('vouchers/destroy', [VoucherController::class, 'destroy'])->name('vouchers.destroy')->middleware('permission:voucher:destroy');

        // Template notifikasi (email / push / in-app)
        Route::get('notification-templates', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'index'])->name('notification_templates');
        Route::get('notification-templates/create', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'create'])->name('notification_templates.create');
        Route::post('notification-templates', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'store'])->name('notification_templates.store');
        Route::post('notification-templates/{id}/delete', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'destroy'])->whereNumber('id')->name('notification_templates.destroy');
        Route::get('notification-templates/{id}/edit', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'edit'])->whereNumber('id')->name('notification_templates.edit');
        Route::post('notification-templates/{id}', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'update'])->whereNumber('id')->name('notification_templates.update');
        Route::post('notification-templates/preview', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'preview'])->name('notification_templates.preview');
        Route::post('notification-templates/{id}/reset', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'reset'])->whereNumber('id')->name('notification_templates.reset');
        Route::post('notification-templates/{id}/duplicate', [\App\Http\Controllers\Admin\Mobile\NotificationTemplateController::class, 'duplicate'])->whereNumber('id')->name('notification_templates.duplicate');

        // Email Builder (pustaka desain email)
        Route::get('email-designs', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'index'])->name('email_designs');
        Route::post('email-designs', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'store'])->name('email_designs.store');
        Route::get('email-designs/{id}/builder', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'builder'])->whereNumber('id')->name('email_designs.builder');
        Route::post('email-designs/{id}/save', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'save'])->whereNumber('id')->name('email_designs.save');
        Route::post('email-designs/{id}/update', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'update'])->whereNumber('id')->name('email_designs.update');
        Route::post('email-designs/{id}/duplicate', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'duplicate'])->whereNumber('id')->name('email_designs.duplicate');
        Route::post('email-designs/destroy', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'destroy'])->name('email_designs.destroy');
        Route::get('email-designs/{id}/preview', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'preview'])->whereNumber('id')->name('email_designs.preview');
        Route::post('email-designs/upload', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'upload'])->name('email_designs.upload');
        Route::post('email-designs/schemes', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'storeScheme'])->name('email_designs.schemes.store');
        Route::post('email-designs/schemes/destroy', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'destroyScheme'])->name('email_designs.schemes.destroy');
        Route::post('email-designs/{id}/test', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'testSend'])->whereNumber('id')->name('email_designs.test');
        Route::post('email-designs/blocks', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'storeBlock'])->name('email_designs.blocks.store');
        Route::post('email-designs/blocks/destroy', [\App\Http\Controllers\Admin\Mobile\EmailDesignController::class, 'destroyBlock'])->name('email_designs.blocks.destroy');

        // Produk (CRUD) — permission-gated
        Route::get('products', [ProductController::class, 'index'])->name('products')->middleware('permission:product:show');
        Route::get('products/data', [ProductController::class, 'data'])->name('products.data')->middleware('permission:product:show');
        Route::get('products/forms', [ProductController::class, 'forms'])->name('products.forms')->middleware('permission:product:show');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:product:create');
        Route::get('products/edit/{id}', [ProductController::class, 'edit'])->whereNumber('id')->name('products.edit')->middleware('permission:product:update');
        Route::post('products/store', [ProductController::class, 'store'])->name('products.store')->middleware('permission:product:create');
        Route::post('products/update/{id}', [ProductController::class, 'update'])->whereNumber('id')->name('products.update')->middleware('permission:product:update');
        Route::post('products/destroy', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('permission:product:destroy');

        // Suplier (master data internal — untuk tracking produk)
        Route::get('suppliers', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'index'])->name('suppliers');
        Route::get('suppliers/data', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'data'])->name('suppliers.data');
        Route::get('suppliers/forms', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'forms'])->name('suppliers.forms');
        Route::post('suppliers/store', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'store'])->name('suppliers.store');
        Route::post('suppliers/update/{id}', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'update'])->whereNumber('id')->name('suppliers.update');
        Route::post('suppliers/destroy', [\App\Http\Controllers\Admin\Mobile\SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::get('products/import/template', [ProductController::class, 'importTemplate'])->name('products.import.template')->middleware('permission:product:create');
        Route::post('products/import/upload', [ProductController::class, 'importUpload'])->name('products.import.upload')->middleware('permission:product:create');
        Route::post('products/import/execute', [ProductController::class, 'importExecute'])->name('products.import.execute')->middleware('permission:product:create');

        // Kategori Produk
        Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product_categories')->middleware('permission:product-category:show');
        Route::get('product-categories/data', [ProductCategoryController::class, 'data'])->name('product_categories.data')->middleware('permission:product-category:show');
        Route::get('product-categories/forms', [ProductCategoryController::class, 'forms'])->name('product_categories.forms')->middleware('permission:product-category:show');
        Route::post('product-categories/store', [ProductCategoryController::class, 'store'])->name('product_categories.store')->middleware('permission:product-category:create');
        Route::post('product-categories/update/{id}', [ProductCategoryController::class, 'update'])->whereNumber('id')->name('product_categories.update')->middleware('permission:product-category:update');
        Route::post('product-categories/destroy', [ProductCategoryController::class, 'destroy'])->name('product_categories.destroy')->middleware('permission:product-category:destroy');

        // Kurir Pengiriman
        Route::get('shipping-couriers', [ShippingCourierController::class, 'index'])->name('shipping_couriers')->middleware('permission:shipping:show');
        Route::get('shipping-couriers/data', [ShippingCourierController::class, 'data'])->name('shipping_couriers.data')->middleware('permission:shipping:show');
        Route::get('shipping-couriers/forms', [ShippingCourierController::class, 'forms'])->name('shipping_couriers.forms')->middleware('permission:shipping:show');
        Route::post('shipping-couriers/store', [ShippingCourierController::class, 'store'])->name('shipping_couriers.store')->middleware('permission:shipping:create');
        Route::post('shipping-couriers/update/{id}', [ShippingCourierController::class, 'update'])->whereNumber('id')->name('shipping_couriers.update')->middleware('permission:shipping:update');
        Route::post('shipping-couriers/destroy', [ShippingCourierController::class, 'destroy'])->name('shipping_couriers.destroy')->middleware('permission:shipping:destroy');

        // Order Produk (kelola)
        Route::get('promotions', [PromotionController::class, 'index'])->name('promotions')->middleware('permission:promotion:show');
        Route::get('promotions/data', [PromotionController::class, 'data'])->name('promotions.data')->middleware('permission:promotion:show');
        Route::get('promotions/forms', [PromotionController::class, 'forms'])->name('promotions.forms')->middleware('permission:promotion:show');
        Route::post('promotions/store', [PromotionController::class, 'store'])->name('promotions.store')->middleware('permission:promotion:create');
        Route::post('promotions/update/{id}', [PromotionController::class, 'update'])->whereNumber('id')->name('promotions.update')->middleware('permission:promotion:update');
        Route::post('promotions/destroy', [PromotionController::class, 'destroy'])->name('promotions.destroy')->middleware('permission:promotion:delete');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories')->middleware('permission:category:show');
        Route::get('categories/forms', [CategoryController::class, 'forms'])->name('categories.forms')->middleware('permission:category:show');
        Route::post('categories/store', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:category:create');
        Route::post('categories/update/{id}', [CategoryController::class, 'update'])->whereNumber('id')->name('categories.update')->middleware('permission:category:update');
        Route::post('categories/destroy', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:category:delete');

        Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home_sections')->middleware('permission:home-section:show');
        Route::get('home-sections/forms', [HomeSectionController::class, 'forms'])->name('home_sections.forms')->middleware('permission:home-section:show');
        Route::get('home-sections/source-items', [HomeSectionController::class, 'sourceItems'])->name('home_sections.source_items')->middleware('permission:home-section:show');
        Route::post('home-sections/store', [HomeSectionController::class, 'store'])->name('home_sections.store')->middleware('permission:home-section:create');
        Route::post('home-sections/update/{id}', [HomeSectionController::class, 'update'])->whereNumber('id')->name('home_sections.update')->middleware('permission:home-section:update');
        Route::post('home-sections/destroy', [HomeSectionController::class, 'destroy'])->name('home_sections.destroy')->middleware('permission:home-section:delete');
        Route::post('home-sections/reorder', [HomeSectionController::class, 'reorder'])->name('home_sections.reorder')->middleware('permission:home-section:update');

        // Proposal (hasil isian form pengajuan)
        Route::get('proposals', [ProposalController::class, 'index'])->name('proposals')->middleware('permission:proposal:show');
        Route::get('proposals/{id}', [ProposalController::class, 'show'])->whereNumber('id')->name('proposals.show')->middleware('permission:proposal:show');
        Route::get('proposals/{id}/pdf', [ProposalController::class, 'pdf'])->whereNumber('id')->name('proposals.pdf')->middleware('permission:proposal:show');
        Route::get('proposals/{id}/download', [ProposalController::class, 'download'])->whereNumber('id')->name('proposals.download')->middleware('permission:proposal:show');
        Route::post('proposals/{id}/status', [ProposalController::class, 'updateStatus'])->whereNumber('id')->name('proposals.status')->middleware('permission:proposal:update');

        // Form builder (khusus layanan)
        Route::get('forms', [FormController::class, 'index'])->name('forms')->middleware('permission:form:show');
        Route::get('forms/{id}/builder', [FormController::class, 'builder'])->whereNumber('id')->name('forms.builder')->middleware('permission:form:show');
        Route::get('forms/{id}/preview', [FormController::class, 'preview'])->whereNumber('id')->name('forms.preview')->middleware('permission:form:show');
        Route::get('forms/forms', [FormController::class, 'forms'])->name('forms.forms')->middleware('permission:form:show');
        Route::post('forms/store', [FormController::class, 'store'])->name('forms.store')->middleware('permission:form:create');
        Route::post('forms/update/{id}', [FormController::class, 'update'])->whereNumber('id')->name('forms.update')->middleware('permission:form:update');
        Route::post('forms/destroy', [FormController::class, 'destroy'])->name('forms.destroy')->middleware('permission:form:delete');
        Route::post('forms/duplicate', [FormController::class, 'duplicate'])->name('forms.duplicate')->middleware('permission:form:create');
        Route::post('forms/fields/store', [FormController::class, 'storeField'])->name('forms.fields.store')->middleware('permission:form:update');
        Route::post('forms/fields/update/{id}', [FormController::class, 'updateField'])->whereNumber('id')->name('forms.fields.update')->middleware('permission:form:update');
        Route::post('forms/fields/destroy', [FormController::class, 'destroyField'])->name('forms.fields.destroy')->middleware('permission:form:update');
        Route::post('forms/fields/reorder', [FormController::class, 'reorderField'])->name('forms.fields.reorder')->middleware('permission:form:update');
        Route::post('forms/fields/reorder-bulk', [FormController::class, 'reorderFields'])->name('forms.fields.reorder_bulk')->middleware('permission:form:update');

        // Template Rules Step (template langkah status pengajuan)
        Route::get('step-templates', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'index'])->name('step_templates');
        Route::get('step-templates/{id}/builder', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'builder'])->whereNumber('id')->name('step_templates.builder');
        Route::post('step-templates/store', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'store'])->name('step_templates.store');
        Route::post('step-templates/update/{id}', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'update'])->whereNumber('id')->name('step_templates.update');
        Route::post('step-templates/destroy', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'destroy'])->name('step_templates.destroy');
        Route::post('step-templates/duplicate', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'duplicate'])->name('step_templates.duplicate');
        Route::post('step-templates/{id}/steps/store', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'storeStep'])->whereNumber('id')->name('step_templates.steps.store');
        Route::post('step-templates/steps/update/{id}', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'updateStep'])->whereNumber('id')->name('step_templates.steps.update');
        Route::post('step-templates/steps/destroy', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'destroyStep'])->name('step_templates.steps.destroy');
        Route::post('step-templates/{id}/steps/reorder', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'reorderSteps'])->whereNumber('id')->name('step_templates.steps.reorder');

        // Koleksi data dinamis (master-data buatan admin → source form builder).
        Route::get('collections', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'index'])->name('collections');
        Route::post('collections/store', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'store'])->name('collections.store');
        Route::get('collections/{id}', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'manage'])->whereNumber('id')->name('collections.manage');
        Route::post('collections/update/{id}', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'update'])->whereNumber('id')->name('collections.update');
        Route::post('collections/destroy', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'destroy'])->name('collections.destroy');
        Route::post('collections/fields/store', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'storeField'])->name('collections.fields.store');
        Route::post('collections/fields/update/{id}', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'updateField'])->whereNumber('id')->name('collections.fields.update');
        Route::post('collections/fields/destroy', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'destroyField'])->name('collections.fields.destroy');
        Route::post('collections/entries/store', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'storeEntry'])->name('collections.entries.store');
        Route::post('collections/entries/update/{id}', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'updateEntry'])->whereNumber('id')->name('collections.entries.update');
        Route::post('collections/entries/destroy', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'destroyEntry'])->name('collections.entries.destroy');
        Route::post('collections/entries/reorder', [\App\Http\Controllers\Admin\Mobile\CollectionController::class, 'reorderEntries'])->name('collections.entries.reorder');

        Route::get('product-orders', [ProductOrderController::class, 'index'])->name('product_orders')->middleware('permission:product-order:show');
        Route::get('product-orders/data', [ProductOrderController::class, 'data'])->name('product_orders.data')->middleware('permission:product-order:show');
        Route::post('product-orders/update/{id}', [ProductOrderController::class, 'update'])->whereNumber('id')->name('product_orders.update')->middleware('permission:product-order:update');
        Route::get('product-orders/{id}', [ProductOrderController::class, 'show'])->whereNumber('id')->name('product_orders.show')->middleware('permission:product-order:show');
        Route::get('product-orders/{id}/chat-user', [ProductOrderController::class, 'chatUser'])->whereNumber('id')->name('product_orders.chat_user')->middleware('permission:product-order:show');
        Route::get('product-orders/{id}/invoice', [ProductOrderController::class, 'invoice'])->whereNumber('id')->name('product_orders.invoice')->middleware('permission:product-order:show');
        Route::get('product-orders-export/excel', [ProductOrderController::class, 'exportExcel'])->name('product_orders.export_excel')->middleware('permission:product-order:show');
        Route::get('product-orders-export/pdf', [ProductOrderController::class, 'exportPdf'])->name('product_orders.export_pdf')->middleware('permission:product-order:show');

        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews')->middleware('permission:product-review:show');
        Route::get('reviews/data', [ReviewController::class, 'data'])->name('reviews.data')->middleware('permission:product-review:show');
        Route::get('reviews/forms', [ReviewController::class, 'forms'])->name('reviews.forms')->middleware('permission:product-review:show');
        Route::get('service-requests', [MobileServiceRequestController::class, 'index'])->name('service_requests.index');
        Route::get('service-requests/data', [MobileServiceRequestController::class, 'data'])->name('service_requests.data');
        Route::get('service-requests/export', [MobileServiceRequestController::class, 'exportExcel'])->name('service_requests.export');
        Route::get('service-requests/export-pdf', [MobileServiceRequestController::class, 'exportPdf'])->name('service_requests.export_pdf');
        Route::get('service-requests/{id}/download', [MobileServiceRequestController::class, 'download'])->whereNumber('id')->name('service_requests.download');
        Route::get('service-requests/{id}/invoice', [MobileServiceRequestController::class, 'invoice'])->whereNumber('id')->name('service_requests.invoice');
        Route::get('service-requests/photos/{file}', [MobileServiceRequestController::class, 'photo'])->where('file', '.+')->name('service_requests.photo');
        Route::post('service-requests/{id}/confirm-payment', [MobileServiceRequestController::class, 'confirmPayment'])->whereNumber('id')->name('service_requests.confirm_payment');
        Route::post('service-requests/{id}/reject-payment', [MobileServiceRequestController::class, 'rejectPayment'])->whereNumber('id')->name('service_requests.reject_payment');
        Route::post('service-requests/{id}/approve', [MobileServiceRequestController::class, 'approve'])->whereNumber('id')->name('service_requests.approve');
        Route::post('service-requests/{id}/complete', [MobileServiceRequestController::class, 'complete'])->whereNumber('id')->name('service_requests.complete');
        Route::post('service-requests/{id}/reject', [MobileServiceRequestController::class, 'reject'])->whereNumber('id')->name('service_requests.reject');
        Route::post('service-requests/{id}/status', [MobileServiceRequestController::class, 'updateStatus'])->whereNumber('id')->name('service_requests.update_status');
        Route::post('service-requests/{id}/steps/complete', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'completeRequestStep'])->whereNumber('id')->name('service_requests.steps.complete');
        Route::post('service-requests/{id}/steps/reopen', [\App\Http\Controllers\Admin\Mobile\StepTemplateController::class, 'reopenRequestStep'])->whereNumber('id')->name('service_requests.steps.reopen');
        Route::get('service-requests/{id}/chat-user', [MobileServiceRequestController::class, 'chatUser'])->whereNumber('id')->name('service_requests.chat_user');
        Route::get('service-requests/{id}', [MobileServiceRequestController::class, 'show'])->whereNumber('id')->name('service_requests.show');
        Route::get('home-layout', [MobileController::class, 'homeLayout'])->name('home_layout');
        Route::get('notifications', [MobileController::class, 'notifications'])->name('notifications');
        Route::get('notifications/send', [MobileController::class, 'sendNotificationForm'])->name('notifications.create');
        Route::post('notifications/send', [MobileController::class, 'sendNotification'])->name('notifications.send');
        Route::prefix('inspirasi')->name('inspirasi.')->group(function () {
            Route::get('', [MobileInspireController::class, 'index'])->name('index');
            Route::get('data', [MobileInspireController::class, 'data'])->name('data');
            Route::get('create', [MobileInspireController::class, 'create'])->name('create');
            Route::post('store', [MobileInspireController::class, 'store'])->name('store');
            Route::get('edit/{slug}', [MobileInspireController::class, 'edit'])->name('edit');
            Route::post('update/{slug}', [MobileInspireController::class, 'update'])->name('update');
            Route::post('destroy', [MobileInspireController::class, 'destroy'])->name('destroy');
        });
        Route::get('live-chat/{conversation?}', [MobileChatController::class, 'index'])->whereNumber('conversation')->name('live_chat');
        Route::post('live-chat/{conversation}/messages', [MobileChatController::class, 'store'])->whereNumber('conversation')->name('live_chat.messages');
        Route::post('live-chat/{conversation}/typing', [MobileChatController::class, 'typing'])->whereNumber('conversation')->name('live_chat.typing');
        Route::get('settings', [MobileController::class, 'settings'])->name('settings');
        Route::post('settings/update', [MobileController::class, 'updateSettings'])->name('settings.update');
        Route::get('regions/provinces', [MobileController::class, 'regionsProvinces'])->name('regions.provinces');
        Route::get('regions/regencies', [MobileController::class, 'regionsRegencies'])->name('regions.regencies');
        Route::get('regions/districts', [MobileController::class, 'regionsDistricts'])->name('regions.districts');
        Route::get('regions/villages', [MobileController::class, 'regionsVillages'])->name('regions.villages');
    });

    // email management
    Route::prefix('email/')->name('email.')->group(function () {
        Route::get('', [EmailManagementController::class, 'index'])->name('index');
        Route::get('contact', [EmailManagementController::class, 'listContactCustomer'])->name('list_contact_customer');
        Route::get('list-message', [EmailManagementController::class, 'listMessage'])->name('list_message');
        Route::post('destroy-message', [EmailManagementController::class, 'destroyMessage'])->name('destroy_message');
        Route::get('show/{id}', [EmailManagementController::class, 'show'])->name('show');
        Route::get('replay-message/{message_id}', [EmailManagementController::class, 'replayMessage'])->name('replay_message');
        Route::get('email-sending', [EmailManagementController::class, 'emailSending'])->name('email_sending');
        Route::get('create-message', [EmailManagementController::class, 'createMessage'])->name('create_message');
        Route::post('send-bulk-message', [EmailManagementController::class, 'sendBulkMessage'])->name('send_bulk_message');
        Route::get('data-email-sending', [EmailManagementController::class, 'dataEmailSending'])->name('data_email_sending');
        Route::get('settings', [EmailManagementController::class, 'settingsEmail'])->name('settings_email');
        Route::post('settings/update', [EmailManagementController::class, 'updateSettingsEmail'])->name('settings_email_update');
        Route::post('send-replay-message/{message_id}', [EmailManagementController::class, 'sendReplayMessage'])->name('send_replay_message');

        // contact
        Route::prefix('contact/')->name('contact.')->group(function () {
            Route::get('', [ContactEmailController::class, 'index'])->name('index');
            Route::get('forms', [ContactEmailController::class, 'forms'])->name('forms');
            Route::post('store', [ContactEmailController::class, 'store'])->name('store');
            Route::post('destroy', [ContactEmailController::class, 'destroy'])->name('destroy');
            Route::post('update', [ContactEmailController::class, 'update'])->name('update');
            Route::post('read-file', [ContactEmailController::class, 'readFile'])->name('read_file');
            Route::post('mapped', [ContactEmailController::class, 'importWithMapping'])->name('mapped');
            Route::get('export', [ContactEmailController::class, 'export'])->name('export');
            Route::get('data', [ContactEmailController::class, 'data'])->name('data');
        });
    });

    // packages
    Route::prefix('packages/')->name('packages.')->group(function () {
        Route::get('', [PackageController::class, 'index'])->name('index');
        Route::get('data', [PackageController::class, 'data'])->name('data');
        Route::get('create', [PackageController::class, 'create'])->name('create');
        Route::post('store', [PackageController::class, 'store'])->name('store');
        Route::post('update/{id}', [PackageController::class, 'update'])->name('update');
        Route::post('destroy', [PackageController::class, 'destroy'])->name('destroy');
        Route::get('edit/{id}', [PackageController::class, 'edit'])->name('edit');
    });

    // tema
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('', [ThemeController::class, 'index'])->name('settings');
        Route::post('update/{id}', [ThemeController::class, 'update'])->name('update');
    });

    // social media
    Route::prefix('social-media/')->name('social_media.')->group(function () {
        Route::get('', [SocialMediaController::class, 'index'])->name('index');
        Route::get('data', [SocialMediaController::class, 'data'])->name('data');
        Route::get('create', [SocialMediaController::class, 'create'])->name('create');
        Route::post('store', [SocialMediaController::class, 'store'])->name('store');
        Route::post('update/{id}', [SocialMediaController::class, 'update'])->name('update');
        Route::post('destroy', [SocialMediaController::class, 'destroy'])->name('destroy');
        Route::get('edit/{id}', [SocialMediaController::class, 'edit'])->name('edit');
    });

    Route::prefix('visitor/')->name('visitor.')->group(function () {
        Route::get('data', [VisitorController::class, 'data'])->name('data');
    });

    // rekomendasi
    Route::prefix('rekomendasi/')->name('rekomendasi_kavling.')->group(function () {
        Route::get('', [RekomendasiKavlingController::class, 'index'])->middleware('permission:rekomendasi:show')->name('index');
        Route::get('data', [RekomendasiKavlingController::class, 'data'])->middleware('permission:rekomendasi:show')->name('data');
        Route::get('create', [RekomendasiKavlingController::class, 'create'])->middleware('permission:rekomendasi:create')->name('create');
        Route::get('{id}', [RekomendasiKavlingController::class, 'edit'])->middleware('permission:rekomendasi:edit')->name('edit');
        Route::post('update/{id}', [RekomendasiKavlingController::class, 'update'])->middleware('permission:rekomendasi:update')->name('update');
        Route::post('store', [RekomendasiKavlingController::class, 'store'])->middleware('permission:rekomendasi:store')->name('store');
        Route::post('destroy', [RekomendasiKavlingController::class, 'destroy'])->middleware('permission:rekomendasi:destroy')->name('destroy');
    });

    Route::post('logout', [AuthenticateController::class, 'logout'])->name('auth.logout');
    Route::post('upload-file', [FileUploadController::class, 'uploadFile'])->name('upload_file');
    Route::post('delete_file', [FileUploadController::class, 'deleteFile'])->name('delete_file');
});
