<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\MobileSupportContactAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MobileSupportContactController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected MobileSupportContactAdminService $service
    ) {
        $this->setView('admin.pages.mobile.support-contacts');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('contact', function ($contact) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">' . e($contact->label) . '</span>
                            <small class="text-muted">' . e($contact->value) . '</small>
                        </div>
                    ';
                })
                ->addColumn('type', fn ($contact) => '<span class="badge badge-light text-uppercase">' . e($contact->type) . '</span>')
                ->addColumn('sort_order', fn ($contact) => (int) $contact->sort_order)
                ->addColumn('status', function ($contact) {
                    if ($contact->is_active) {
                        return '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('action', function ($contact) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-mobile-support-contact="' . $contact->id . '" class="btn btn-success btn-xs editMobileSupportContact" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteMobileSupportContact(' . $contact->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['contact', 'type', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $contact = null;

        try {
            if ($request->filled('id_mobile_support_contact')) {
                $contact = $this->service->find((int) $request->id_mobile_support_contact);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('contact'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validatePayload($request);
            $this->service->create($validated);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan kontak dukungan.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validated = $this->validatePayload($request);
            $this->service->update($id, $validated);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah kontak dukungan.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_mobile_support_contact);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus kontak dukungan.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:whatsapp,email,phone,instagram,other'],
            'label' => ['required', 'string', 'max:120'],
            'value' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
