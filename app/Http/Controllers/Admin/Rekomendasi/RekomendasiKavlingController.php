<?php

namespace App\Http\Controllers\Admin\Rekomendasi;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Services\RekomendasiKavlingService;
use RealRashid\SweetAlert\Facades\Alert;

class RekomendasiKavlingController extends Controller
{
    use AdminView;

    public function __construct(
        private RekomendasiKavlingService $rekomendasi
    ) {
        $this->setView('admin.pages.rekomendasi-kavling');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {

            return DataTables::of($this->rekomendasi->getAll())
                ->addColumn('title', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->title . '</span>';
                })
                ->addColumn('slug', function ($list) {
                    return '<span class="d-block text-truncate" style="max-width: 150px;">' . $list->slug . '</span>';
                })
                ->addColumn('created_by', function ($list) {
                    return $list->createdBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('updated_by', function ($list) {
                    return $list->updatedBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('action', function ($list) {
                    return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="'.(route('admin.rekomendasi_kavling.edit', $list->id)).'" data-bind-rekomendasi="' . $list->id . '" class="btn btn-success btn-xs editRekomendasi" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteRekomendasi(' . $list->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['title', 'slug', 'created_by', 'updated_by', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create() {
        return $this->view('create');
    }

    public function edit($id) {
        $rekomendasi = $this->rekomendasi->findKavling($id);
        return $this->view('edit', [
            'rekomendasi' => $rekomendasi
        ]);
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'images' => 'required|array|min:1',
            'images.*' => 'mimes:jpeg,png,jpg,gif,svg,webp'
        ], [
            'title.required' => 'Judul harus diisi',
            'title.max' => 'Judul tidak boleh lebih dari 150 karakter',
            'title.string' => 'Judul harus berupa string yang valid',
            'content.required' => 'Content harus diisi',
            'content.string' => 'Content harus berupa string yang valid',
            'images.required' => 'Gambar harus diisi',
            'images.array' => 'Gambar harus berupa array',
            'images.min' => 'Gambar minimal 1 item',
            'images.mimes' => 'Gambar yang diperbolehkan hanya (.jpeg, .png, .jpg, .gif, .svg, .webp)'
        ]);

        try {

            if(count($request->fixed_images ?? []) <= 0) throw new \Exception('Upload gambar minimal 1 item.');

            $this->rekomendasi
            ->setRequest($request)
            ->store();

            Alert::success('Berhasil', 'Data berhasil disimpan');
            return redirect()->route('admin.rekomendasi_kavling.index');

        }catch(\Throwable $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function update(Request $request, $id) {

        $request->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'fixed_images' => 'array|min:1|required',
            'images' => 'array|min:1',
            'images.*' => 'mimes:jpeg,png,jpg,gif,svg,webp'
        ], [
            'title.required' => 'Judul harus diisi',
            'title.max' => 'Judul tidak boleh lebih dari 150 karakter',
            'title.string' => 'Judul harus berupa string yang valid',
            'content.required' => 'Content harus diisi',
            'content.string' => 'Content harus berupa string yang valid',
            'images.array' => 'Gambar harus berupa array',
            'images.min' => 'Gambar minimal 1 item',
            'images.mimes' => 'Gambar yang diperbolehkan hanya (.jpeg, .png, .jpg, .gif, .svg, .webp)',
            'fixed_images.array' => 'Gambar harus berupa array',
            'fixed_images.min' => 'Gambar minimal 1 item',
            'fixed_images.required' => 'Gambar harus diisi'
        ]);

        try {

            if(count($request->fixed_images ?? []) <= 0) throw new \Exception('Upload gambar minimal 1 item.');

            $this->rekomendasi
            ->setRequest($request)
            ->update($id);

            Alert::success('Berhasil', 'Data berhasil disimpan');
            return redirect()->route('admin.rekomendasi_kavling.index');

        }catch(\Throwable $e) {
            Alert::error('Error!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        try {

            $this->rekomendasi->delete($request->id);
            
            return response()->json([
                'message' => 'Data berhasil dihapus'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
