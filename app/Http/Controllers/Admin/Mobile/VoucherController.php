<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Services\VoucherAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected VoucherAdminService $service
    ) {
        $this->setView('admin.pages.mobile.vouchers');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->service->queryForAdmin())
                ->addColumn('voucher', function ($voucher) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-uppercase">' . e($voucher->code) . '</span>
                            <small class="text-muted">' . e($voucher->name) . '</small>
                        </div>
                    ';
                })
                ->addColumn('order_type', fn ($voucher) => '<span class="badge badge-light text-uppercase">' . e($voucher->order_type) . '</span>')
                ->addColumn('discount', function ($voucher) {
                    if ($voucher->discount_type === 'percentage') {
                        $cap = $voucher->max_discount_amount ? ' (maks Rp' . number_format($voucher->max_discount_amount, 0, ',', '.') . ')' : '';

                        return $voucher->discount_value . '%' . $cap;
                    }

                    return 'Rp' . number_format($voucher->discount_value, 0, ',', '.');
                })
                ->addColumn('quota', function ($voucher) {
                    $total = $voucher->usage_limit ? $voucher->used_count . '/' . $voucher->usage_limit : $voucher->used_count . '/∞';

                    return $total . ' <small class="text-muted">(' . $voucher->usage_limit_per_user . '/user)</small>';
                })
                ->addColumn('expires', fn ($voucher) => $voucher->expires_at ? $voucher->expires_at->format('d M Y') : '—')
                ->addColumn('status', function ($voucher) {
                    $expired = $voucher->expires_at && $voucher->expires_at->isPast();
                    if ($expired) {
                        return '<span class="badge badge-warning badge-sm">Expired</span>';
                    }

                    return $voucher->is_active
                        ? '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>'
                        : '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('action', function ($voucher) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-voucher="' . $voucher->id . '" class="btn btn-success btn-xs editVoucher" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteVoucher(' . $voucher->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['voucher', 'order_type', 'discount', 'quota', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $voucher = null;

        try {
            if ($request->filled('id_voucher')) {
                $voucher = $this->service->find((int) $request->id_voucher);
            }

            $services = $this->service->services();
            $users = $this->service->users();

            return $this->setView('admin.components.forms.')->view($request->view, compact('voucher', 'services', 'users'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            [$data, $serviceIds, $userIds] = $this->validatePayload($request);
            $this->service->create($data, $serviceIds, $userIds);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menambahkan voucher.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            [$data, $serviceIds, $userIds] = $this->validatePayload($request, $id);
            $this->service->update($id, $data, $serviceIds, $userIds);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil mengubah voucher.'], $this->statusCode);
        } catch (ValidationException $error) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->service->delete((int) $request->id_voucher);
            $this->statusCode = 200;

            return response()->json(['message' => 'Berhasil menghapus voucher.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }

    private function validatePayload(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('vouchers', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'terms' => ['nullable', 'string', 'max:20000'],
            'order_type' => ['required', 'in:service,product'],
            'discount_type' => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'max_discount_amount' => ['nullable', 'integer', 'min:0'],
            'min_purchase_amount' => ['nullable', 'integer', 'min:0'],
            'item_scope' => ['required', 'in:all,specific'],
            'user_scope' => ['required', 'in:all,specific'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['required', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
            'target_service_ids' => ['nullable', 'array'],
            'target_service_ids.*' => ['integer'],
            'target_user_ids' => ['nullable', 'array'],
            'target_user_ids.*' => ['integer'],
        ]);

        if ($validated['discount_type'] === 'percentage' && (int) $validated['discount_value'] > 100) {
            throw ValidationException::withMessages(['discount_value' => 'Persentase maksimal 100%.']);
        }

        $data = collect($validated)->except(['target_service_ids', 'target_user_ids'])->toArray();
        $data['max_discount_amount'] = $validated['discount_type'] === 'percentage' ? ($validated['max_discount_amount'] ?? null) : null;
        $data['min_purchase_amount'] = $validated['min_purchase_amount'] ?? 0;

        return [$data, $validated['target_service_ids'] ?? [], $validated['target_user_ids'] ?? []];
    }
}
