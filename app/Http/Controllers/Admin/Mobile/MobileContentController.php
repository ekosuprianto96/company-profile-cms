<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobileContent;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileContentController extends Controller
{
    use AdminView;

    public function __construct()
    {
        $this->setView('admin.pages.mobile.contents');
    }

    public function index()
    {
        $contents = MobileContent::query()->whereIn('key', ['about', 'terms'])->get()->keyBy('key');

        return $this->view('index', [
            'about' => $contents->get('about'),
            'terms' => $contents->get('terms'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'about_body' => ['nullable', 'string'],
            'terms_body' => ['nullable', 'string'],
        ]);

        MobileContent::query()->updateOrCreate(
            ['key' => 'about'],
            ['title' => 'Tentang Aplikasi', 'body' => $validated['about_body'] ?? '', 'updated_by' => Auth::id()]
        );

        MobileContent::query()->updateOrCreate(
            ['key' => 'terms'],
            ['title' => 'Syarat & Ketentuan', 'body' => $validated['terms_body'] ?? '', 'updated_by' => Auth::id()]
        );

        return redirect()->route('admin.mobile.contents')->with('success', 'Konten aplikasi berhasil diperbarui.');
    }
}
