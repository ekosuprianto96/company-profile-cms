<?php

namespace App\Http\Controllers\Admin\Blogs;

use App\Traits\AdminView;
use Illuminate\Http\Request;
use App\Services\BlogService;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Requests\BlogStoreRequest;
use App\Http\Requests\BlogUpdteRequest;
use RealRashid\SweetAlert\Facades\Alert;

class BlogController extends Controller
{
    use AdminView;

    protected $statusCode = 500;
    public function __construct(
        public BlogService $blogService
    ) {
        $this->setView('admin.pages.blogs');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function createBlog()
    {
        return $this->view('create');
    }

    public function storeBlog(BlogStoreRequest $request)
    {
        try {

            $blog = $this->blogService->create($request);
            $this->statusCode = 200;

            Alert::success(
                'Succes',
                'Blog berhasil di simpan dengan status: ' . ((bool) $blog->an ? Status::PUBLISH->text() : Status::UNPUBLISH->text())
            );

            return redirect()->route('admin.blogs.index');
        } catch (\Exception $e) {

            Alert::error('Gagal!', $e->getMessage());
            return redirect()->back();
        }
    }

    public function editBlog($slug)
    {
        $data['blog'] = $this->blogService->findBySlug($slug);
        return $this->view('edit', $data);
    }

    public function updateBlog(BlogUpdteRequest $request, $slug)
    {
        try {

            $update = $this->blogService->update($request, $slug);
            $this->statusCode = 200;

            Alert::success('Sukses', 'Postingan beruhasil diperbarui.');
            return redirect()->route('admin.blogs.index');
        } catch (\Exception $e) {
            Alert::error('Gagal!', $e->getMessage());

            return redirect()->back();
        }
    }

    public function destroy(Request $request)
    {
        try {

            $this->blogService->delete($request->slug);
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Postingan berhasil dihapus.',
                'redirect_url' => route('admin.blogs.index')
            ], $this->statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }

    public function data(Request $request)
    {
        try {

            return DataTables::of($this->blogService->all())
                ->addColumn('thumbnail', function ($list) {
                    return '<img src="' . image_url('blogs', $list->thumbnail) . '" alt="' . $list->thumbnail . '">';
                })
                ->addColumn('title', function ($list) {
                    return '<span title="' . $list->title . '" class="d-block text-truncate" style="max-width: 200px;">' . $list->title . '</span>';
                })
                ->addColumn('kategori', function ($list) {
                    return '<span class="badge badge-outline-warning rounded badge-sm">' . $list->kategori->name . '</span>';
                })
                ->addColumn('slug', function ($list) {
                    return '<span title="' . $list->slug . '" class="d-block text-truncate" style="max-width: 200px;">' . $list->slug . '</span>';
                })
                ->addColumn('created_by', function ($list) {
                    return $list->createdBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('updated_by', function ($list) {
                    return $list->updatedBy->account->nama_lengkap ?? '-';
                })
                ->addColumn('status', function ($list) {
                    if ($list->an === 1) {
                        return '<span class="badge badge-success badge-sm">' . (Status::PUBLISH->text()) . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . (Status::UNPUBLISH->text()) . '</span>';;
                })
                ->addColumn('action', function ($list) {
                    return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                        <a href="' . route('admin.blogs.edit', $list->slug) . '" class="btn btn-success btn-xs" title="Edit"><i class="ri-pencil-line"></i></a>
                    </div>
                ';
                })
                ->rawColumns(['thumbnail', 'title', 'kategori', 'slug', 'created_by', 'updated_by', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $this->statusCode);
        }
    }
}
