<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\MobileEventProjectAdminService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class MobileEventProjectController extends Controller
{
    use AdminView;

    public function __construct(
        protected MobileEventProjectAdminService $eventProjectAdminService
    ) {
        $this->setView('admin.pages.mobile.event-projects');
    }

    public function index()
    {
        return $this->view('index', [
            'types' => $this->eventProjectAdminService->queryTypes()->get(),
            'needs' => $this->eventProjectAdminService->queryNeeds()->get(),
            'packages' => $this->eventProjectAdminService->queryPackages()->get(),
            'budgets' => $this->eventProjectAdminService->queryBudgets()->get(),
        ]);
    }

    public function storeType(Request $request)
    {
        $this->eventProjectAdminService->createType($request->validate($this->basicRules()));

        return back()->with('success', 'Jenis project event berhasil ditambahkan.');
    }

    public function updateType(Request $request, int $id)
    {
        $this->eventProjectAdminService->updateType($id, $request->validate($this->basicRules()));

        return back()->with('success', 'Jenis project event berhasil diperbarui.');
    }

    public function destroyType(int $id)
    {
        $this->eventProjectAdminService->deleteType($id);

        return back()->with('success', 'Jenis project event berhasil dihapus.');
    }

    public function storeNeed(Request $request)
    {
        $this->eventProjectAdminService->createNeed($request->validate($this->needRules()));

        return back()->with('success', 'Kebutuhan event berhasil ditambahkan.');
    }

    public function updateNeed(Request $request, int $id)
    {
        $this->eventProjectAdminService->updateNeed($id, $request->validate($this->needRules()));

        return back()->with('success', 'Kebutuhan event berhasil diperbarui.');
    }

    public function destroyNeed(int $id)
    {
        $this->eventProjectAdminService->deleteNeed($id);

        return back()->with('success', 'Kebutuhan event berhasil dihapus.');
    }

    public function storePackage(Request $request)
    {
        $this->eventProjectAdminService->createPackage($request->validate($this->packageRules()));

        return back()->with('success', 'Paket event berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, int $id)
    {
        $this->eventProjectAdminService->updatePackage($id, $request->validate($this->packageRules()));

        return back()->with('success', 'Paket event berhasil diperbarui.');
    }

    public function destroyPackage(int $id)
    {
        $this->eventProjectAdminService->deletePackage($id);

        return back()->with('success', 'Paket event berhasil dihapus.');
    }

    public function storeBudget(Request $request)
    {
        $this->eventProjectAdminService->createBudget($request->validate($this->budgetRules()));

        return back()->with('success', 'Anggaran event berhasil ditambahkan.');
    }

    public function updateBudget(Request $request, int $id)
    {
        $this->eventProjectAdminService->updateBudget($id, $request->validate($this->budgetRules()));

        return back()->with('success', 'Anggaran event berhasil diperbarui.');
    }

    public function destroyBudget(int $id)
    {
        $this->eventProjectAdminService->deleteBudget($id);

        return back()->with('success', 'Anggaran event berhasil dihapus.');
    }

    private function basicRules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'required|boolean',
        ];
    }

    private function needRules(): array
    {
        return array_merge($this->basicRules(), [
            'mobile_event_project_type_id' => 'required|exists:mobile_event_project_types,id',
        ]);
    }

    private function packageRules(): array
    {
        return [
            'mobile_event_project_need_id' => 'required|exists:mobile_event_project_needs,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'required|boolean',
        ];
    }

    private function budgetRules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'min_amount' => 'nullable|integer|min:0|max:999999999999',
            'max_amount' => 'nullable|integer|min:0|max:999999999999',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'is_active' => 'required|boolean',
        ];
    }
}
