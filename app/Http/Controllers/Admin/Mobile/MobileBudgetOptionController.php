<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Admin\Modules\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobileBudgetOptionStoreRequest;
use App\Http\Requests\MobileBudgetOptionUpdateRequest;
use App\Services\MobileBudgetOptionAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MobileBudgetOptionController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected MobileBudgetOptionAdminService $mobileBudgetOptionAdminService
    ) {
        $this->setView('admin.pages.mobile.budget-options');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function data()
    {
        try {
            return DataTables::of($this->mobileBudgetOptionAdminService->queryForAdmin())
                ->addColumn('name', function ($budgetOption) {
                    return '
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">' . e($budgetOption->name) . '</span>
                            <small class="text-muted">' . e($budgetOption->slug) . '</small>
                        </div>
                    ';
                })
                ->addColumn('range', function ($budgetOption) {
                    $minAmount = $budgetOption->min_amount;
                    $maxAmount = $budgetOption->max_amount;

                    if ($minAmount !== null && $maxAmount !== null) {
                        return e($this->formatRupiah((int) $minAmount) . ' - ' . $this->formatRupiah((int) $maxAmount));
                    }

                    if ($minAmount !== null) {
                        return e('>= ' . $this->formatRupiah((int) $minAmount));
                    }

                    if ($maxAmount !== null) {
                        return e('<= ' . $this->formatRupiah((int) $maxAmount));
                    }

                    return '-';
                })
                ->addColumn('sort_order', fn ($budgetOption) => (int) $budgetOption->sort_order)
                ->addColumn('status', function ($budgetOption) {
                    if ($budgetOption->is_active) {
                        return '<span class="badge badge-success badge-sm">' . Status::AKTIF->text() . '</span>';
                    }

                    return '<span class="badge badge-danger badge-sm">' . Status::NONAKTIF->text() . '</span>';
                })
                ->addColumn('action', function ($budgetOption) {
                    return '
                        <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                            <a href="javascript:void(0)" data-bind-mobile-budget-option="' . $budgetOption->id . '" class="btn btn-success btn-xs editMobileBudgetOption" title="Edit"><i class="ri-pencil-line"></i></a>
                            <a href="javascript:void(0)" onclick="deleteMobileBudgetOption(' . $budgetOption->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['name', 'status', 'action'])
                ->make(true);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function forms(Request $request)
    {
        $budgetOption = null;

        try {
            if ($request->filled('id_mobile_budget_option')) {
                $budgetOption = $this->mobileBudgetOptionAdminService->find((int) $request->id_mobile_budget_option);
            }

            return $this->setView('admin.components.forms.')
                ->view($request->view, compact('budgetOption'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(MobileBudgetOptionStoreRequest $request)
    {
        try {
            $this->mobileBudgetOptionAdminService->create($request->validated());
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil menambahkan pilihan anggaran.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function update(MobileBudgetOptionUpdateRequest $request, int $id)
    {
        try {
            $this->mobileBudgetOptionAdminService->update($id, $request->validated());
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil mengubah pilihan anggaran.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = (int) $request->id_mobile_budget_option;
            $this->mobileBudgetOptionAdminService->delete($id);
            $this->statusCode = 200;

            return response()->json([
                'message' => 'Berhasil menghapus pilihan anggaran.'
            ], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json([
                'message' => $error->getMessage()
            ], $this->statusCode);
        }
    }

    private function formatRupiah(int $amount): string
    {
        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}

