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
use App\Http\Controllers\Admin\Groups\GroupController;
use App\Http\Controllers\Admin\Auth\PenggunaController;
use App\Http\Controllers\Admin\InformasiPageController;
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
        EmailMessageService $message
    ) {
        $countPost = $blog->getCount();
        $userCount = User::count();
        $visitorCount = Visitor::count();
        $unreadMessages = $message->getAllMessage(function ($query) {
            return $query->where('is_read', 0);
        });

        return view('admin.pages.dashboard.index', [
            'chart' => $chart->build(),
            'countPost' => $countPost,
            'userCount' => $userCount,
            'unreadMessages' => $unreadMessages,
            'visitorCount' => $visitorCount
        ]);
    })->name('dashboard');

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
        Route::get('/data', [PenggunaController::class, 'data'])->name('data');
        Route::post('/destroy', [PenggunaController::class, 'destroy'])->name('destroy')->middleware('permission:pengguna:destroy');
    });

    // profile
    Route::prefix('profile/')->name('profile.')->group(function () {
        Route::get('', [ProfileController::class, 'index'])->name('index');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
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

    Route::post('logout', [AuthenticateController::class, 'logout'])->name('auth.logout');
    Route::post('upload-file', [FileUploadController::class, 'uploadFile'])->name('upload_file');
    Route::post('delete_file', [FileUploadController::class, 'deleteFile'])->name('delete_file');
});
