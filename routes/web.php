<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Admin\Auth\AuthenticateController;
use App\Http\Controllers\Frontend\DevelController;
use App\Http\Controllers\Frontend\Pages\Home\HomeController;
use App\Http\Controllers\Frontend\Pages\Blogs\BlogController;
use App\Http\Controllers\Frontend\Pages\GalleryController;
use App\Http\Controllers\Frontend\Pages\KontakController;
use App\Http\Controllers\Frontend\Pages\TentangKamiController;
use App\Http\Controllers\Frontend\Pages\Layanan\LayananController;

Route::get('/sitemap', function () {
    $path = public_path('sitemap.xml');

    if (!File::exists($path)) {
        abort(404);
    }

    return Response::file($path, ['Content-Type' => 'application/xml']);
});

Route::get('/', [HomeController::class, 'index'])->middleware(['track-visitor'])->name('home');
Route::get('/layanan', [LayananController::class, 'index'])->middleware(['track-visitor'])->name('layanan');
Route::get('/layanan/{slug}', [LayananController::class, 'show'])->middleware(['track-visitor'])->name('layanan.show');
Route::get('/layanan/{slug}/{widget}', [LayananController::class, 'showWidget'])->middleware(['track-visitor'])->name('layanan.show.widget');
Route::get('/tentang-kami', [TentangKamiController::class, 'index'])->middleware(['track-visitor'])->name('tentang_kami');
Route::get('/galeri', [GalleryController::class, 'index'])->middleware(['track-visitor'])->name('galeri');
Route::get('/galeri/{show}', [GalleryController::class, 'show'])->middleware(['track-visitor'])->name('galeri.show');
Route::get('/blog', [BlogController::class, 'index'])->middleware(['track-visitor'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->middleware(['track-visitor'])->name('blog.show');
Route::get('/kontak-kami', [KontakController::class, 'index'])->middleware(['track-visitor'])->name('kontak');
Route::post('/kontak-kami/send', [KontakController::class, 'sendingEmail'])->middleware(['track-visitor'])->name('kontak.send_email');
Route::post('/kontak-kami/inquiry', [KontakController::class, 'sendingInquiry'])->middleware(['track-visitor'])->name('kontak.send_inquiry');
Route::get('package/{id}', [HomeController::class, 'packageRedirect'])->middleware(['track-visitor'])->name('package.redirect');
// Route::get('/devel', [DevelController::class, 'devel'])->middleware(['track-visitor'])->name('devel');
