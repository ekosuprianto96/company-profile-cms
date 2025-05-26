<?php

namespace App\Http\Controllers\Admin\Packages;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\PackageService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class PackageController extends Controller
{
    use AdminView;

    public function __construct(
        private PackageService $packageService
    ) {
        $this->setView('admin.pages.paket-harga');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function create()
    {
        return $this->view('create');
    }

    public function data(Request $request)
    {
        try {

            return DataTables::of($this->packageService->getAllPackage())
                ->addColumn('name', fn($item) => $item['name'])
                ->addColumn('action', function ($item) {
                    return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="' . route('admin.packages.edit', $item['id']) . '" class="btn btn-success btn-xs editGaleri" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deletePackage(\'' . $item['id'] . '\')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {

            $package = $this->packageService->findPaket($id);

            return $this->view('edit', [
                'package' => $package
            ]);
        } catch (\Throwable $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function store(
        Request $request
    ) {

        $request->validate([
            'name' => 'required|string|min:3|max:50',
            'price' => 'nullable|numeric',
            'is_recommended' => 'nullable|in:0,1',
            'description' => 'required|string|min:3|max:255',
            'show_price' => 'required|in:0,1',
            'features' => 'required|array'
        ], [
            'name.required' => 'Nama paket harus diisi',
            'name.string' => 'Nama paket harus berupa string',
            'name.min' => 'Nama paket minimal 3 karakter',
            'name.max' => 'Nama paket maksimal 50 karakter',
            'price.numeric' => 'Harga harus berupa angka',
            'is_recommended.in' => 'Pilihan tidak valid',
            'description.string' => 'Keterangan harus berupa string',
            'description.min' => 'Keterangan minimal 3 karakter',
            'description.max' => 'Keterangan maksimal 255 karakter',
            'show_price.in' => 'Pilihan tidak valid',
            'features.required' => 'Buat fitur paket minimal 1 item',
            'features.array' => 'Buat fitur paket minimal 1 item'
        ]);

        try {

            DB::transaction(function () use ($request) {
                $this->packageService
                    ->setRequest($request)
                    ->create();
            });

            Alert::success('Success!', 'Paket harga berhasil ditambahkan.');

            return redirect()->route('admin.packages.index');
        } catch (\Throwable $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function update(
        Request $request,
        string $id
    ) {

        $request->validate([
            'name' => 'required|string|min:3|max:50',
            'price' => 'nullable|numeric',
            'is_recommended' => 'nullable|in:0,1',
            'description' => 'required|string|min:3|max:255',
            'show_price' => 'required|in:0,1',
            'features' => 'required|array'
        ], [
            'name.required' => 'Nama paket harus diisi',
            'name.string' => 'Nama paket harus berupa string',
            'name.min' => 'Nama paket minimal 3 karakter',
            'name.max' => 'Nama paket maksimal 50 karakter',
            'price.numeric' => 'Harga harus berupa angka',
            'is_recommended.in' => 'Pilihan tidak valid',
            'description.string' => 'Keterangan harus berupa string',
            'description.min' => 'Keterangan minimal 3 karakter',
            'description.max' => 'Keterangan maksimal 255 karakter',
            'show_price.in' => 'Pilihan tidak valid',
            'features.required' => 'Buat fitur paket minimal 1 item',
            'features.array' => 'Buat fitur paket minimal 1 item'
        ]);

        try {

            DB::transaction(function () use ($request, $id) {
                $this->packageService
                    ->setRequest($request)
                    ->update($id);
            });

            Alert::success('Success!', 'Paket harga berhasil diperbarui.');

            return redirect()->route('admin.packages.index');
        } catch (\Throwable $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {
                $this->packageService
                    ->setRequest($request)
                    ->delete();
            });

            return response()->json([
                'message' => 'Paket berhasil dihapus.'
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
