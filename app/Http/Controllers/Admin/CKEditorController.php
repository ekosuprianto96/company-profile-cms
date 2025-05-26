<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class CKEditorController extends Controller
{
    public function upload(Request $request)
    {
        try {
            if ($request->hasFile('upload')) {
                $file = $request->file('upload');
                $filename = time() . '_' . $file->getClientOriginalName();

                if (!is_dir('assets/images/editors/')) {
                    mkdir('assets/images/editors/', 0777, true);
                }

                $file->move('assets/images/editors/', $filename);

                return response()->json([
                    'url' => asset('assets/images/editors/' . $filename),
                ]);
            }

            throw new \Exception('No file uploaded.', 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function cleanup(Request $request)
    {
        $usedImages = $request->input('images', []);
        $directory = public_path('assets/images/editors/');
        $allImages = collect(File::allFiles($directory))
            ->map(fn($file) => asset('assets/images/editors/' . $file->getFilename()));

        // Hapus gambar yang tidak dipakai
        foreach ($allImages as $image) {
            if (in_array($image, $usedImages)) {
                $filePath = str_replace(asset('/'), public_path('/'), $image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        return response()->json(['message' => 'Cleanup selesai']);
    }
}
