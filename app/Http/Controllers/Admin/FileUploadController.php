<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileUploadController extends Controller
{
    protected $statusCode = 500;
    protected $path = 'assets/images/temps/';

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png|max:2048'
        ], [
            'file.required' => 'File is required',
            'file.mimes' => 'File must be in jpg, jpeg, png format',
            'file.max' => 'File size must be less than 2MB'
        ]);

        try {


            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();
                $newName = now()->format('Y-m-d') . '-' . Str::uuid() . '.' . $ext;

                if (!is_dir(public_path($this->path))) {
                    mkdir(public_path($this->path), 0777, true);
                }

                move_uploaded_file($file, public_path($this->path . $newName));
                $this->statusCode = 200;
                return response()->json([
                    'message' => 'success',
                    'data' => [
                        'file_name' => $newName
                    ]
                ], $this->statusCode);
            }

            $this->statusCode = 400;
            throw new \Exception('File not found', $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function deleteFile(Request $request)
    {
        try {

            if (!key_exists('file_name', $request->all())) {
                $this->statusCode = 404;
                throw new \Exception('Maaf, file_name tidak ditemukan.');
            }

            if (isset($request->edit) && $request->edit == 1) {
                if (file_exists(public_path($request->file_name))) {
                    unlink($request->file_name);
                    $this->statusCode = 200;
                    return response()->json([
                        'message' => 'success'
                    ], $this->statusCode);
                }
            } else {
                if (file_exists(public_path($this->path . $request->file_name))) {
                    unlink(public_path($this->path . $request->file_name));
                    $this->statusCode = 200;
                    return response()->json([
                        'message' => 'success'
                    ], $this->statusCode);
                }
            }

            $this->statusCode = 404;
            throw new \Exception('Maaf, file tidak ditemukan.');
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }
}
