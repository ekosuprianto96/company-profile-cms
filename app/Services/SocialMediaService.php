<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\SocialMediaRepository;

class SocialMediaService
{
    public function __construct(
        public SocialMediaRepository $repository,
        public ?Request $request = null
    ) {}

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function validate(bool $update = false)
    {
        // Implement validation logic here
        $this->request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|url|max:255',
            'icon' => 'required|string|max:255',
            'action_target' => 'required|in:_blank,_self,_parent,_top',
            'an' => 'required|in:0,1'
        ], [
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa string.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'link.required' => 'Link harus diisi.',
            'link.string' => 'Link harus berupa string.',
            'link.url' => 'Link harus berupa URL.',
            'link.max' => 'Link maksimal 255 karakter.',
            'icon.required' => 'Icon harus diisi.',
            'icon.string' => 'Icon harus berupa string.',
            'icon.max' => 'Icon maksimal 255 karakter.',
            'action_target.required' => 'Action target harus diisi.',
            'action_target.in' => 'Action target harus berupa _blank, _self, _parent, atau _top.',
            'an.required' => 'AN harus diisi.',
            'an.in' => 'AN harus berupa 0 atau 1.'
        ]);

        return $this;
    }

    public function getAll(?callable $closure = null)
    {
        return $this->repository->getAll($closure);
    }

    public function dataTable()
    {
        $data = $this->repository->getAll();

        return DataTables::of($data)
            ->addColumn('name', fn($item) => $item['name'])
            ->addColumn('link', fn($item) => '<a href="' . $item['link'] . '" target="_blank">' . $item['link'] . '</a>')
            ->addColumn('icon', fn($item) => '<i class="' . $item['icon'] . '"></i>')
            ->addColumn('an', fn($item) => '<span class="badge badge-' . ($item['an'] == 1 ? 'success' : 'danger') . '">' . ($item['an'] == 1 ? 'Aktif' : 'Tidak Aktif') . '</span>')
            ->addColumn('action', function ($item) {
                return '
                    <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                        <a href="' . (route('admin.social_media.edit', $item['id'])) . '" class="btn btn-success btn-xs editSocial" title="Edit"><i class="ri-pencil-line"></i></a>
                        <a href="javascript:void(0)" onclick="deleteSocialMedia(' . $item['id'] . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                    </div>
                ';
            })
            ->rawColumns(['icon', 'action', 'link', 'an'])
            ->make(true);
    }

    public function findSocialMedia(string $id)
    {
        return $this->repository->find($id);
    }

    public function create()
    {
        $data = $this->request->only('name', 'link', 'icon', 'action_target', 'an');
        return $this->repository->create([
            'id' => Str::uuid(),
            ...$data
        ]);
    }

    public function update(string $id)
    {
        $data = $this->request->only('name', 'link', 'icon', 'action_target', 'an');
        return $this->repository->update($id, $data);
    }

    public function delete()
    {
        return $this->repository->delete($this->request->id);
    }
}
