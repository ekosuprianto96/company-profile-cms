<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Exports\MobileServiceRequestsExport;
use App\Http\Controllers\Controller;
use App\Services\MobileServiceRequestAdminService;
use App\Services\MobileServiceRequestPdfService;
use App\Repositories\MobileServiceRepository;
use App\Traits\AdminView;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class MobileServiceRequestController extends Controller
{
    use AdminView;

    public function __construct(
        protected MobileServiceRequestAdminService $mobileServiceRequestAdminService,
        protected MobileServiceRequestPdfService $mobileServiceRequestPdfService,
        protected MobileServiceRepository $mobileServiceRepository
    ) {
        $this->setView('admin.pages.mobile');
    }

    public function index()
    {
        return $this->view('service-requests.index', [
            'sections' => $this->sections(),
            'services' => $this->mobileServiceRepository->queryForAdmin()
                ->select(['id', 'title'])
                ->orderBy('title')
                ->get(),
            'statusOptions' => [
                ['value' => '', 'label' => 'Semua Status'],
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'waiting_payment', 'label' => 'Waiting Payment'],
                ['value' => 'waiting_transfer', 'label' => 'Waiting Transfer'],
                ['value' => 'payment_challenge', 'label' => 'Payment Challenge'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'rejected', 'label' => 'Rejected'],
                ['value' => 'failed', 'label' => 'Failed'],
            ],
            'paymentStatusOptions' => [
                ['value' => '', 'label' => 'Semua Pembayaran'],
                ['value' => 'unpaid', 'label' => 'Unpaid'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'challenge', 'label' => 'Challenge'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'failed', 'label' => 'Failed'],
                ['value' => 'waiting_transfer', 'label' => 'Waiting Transfer'],
            ],
        ]);
    }

    public function data()
    {
        $renderBadge = function (string $text, string $background, string $color, string $border) {
            return '<span class="d-inline-flex align-items-center rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px; line-height: 1.2; background: ' . e($background) . '; color: ' . e($color) . '; border: 1px solid ' . e($border) . ';">' . e($text) . '</span>';
        };

        $statusStyles = [
            'draft' => ['#e2e8f0', '#334155', '#cbd5e1'],
            'waiting_payment' => ['#eff6ff', '#1d4ed8', '#bfdbfe'],
            'waiting_transfer' => ['#fff7ed', '#c2410c', '#fdba74'],
            'payment_challenge' => ['#fef2f2', '#b91c1c', '#fecaca'],
            'approved' => ['#ecfdf5', '#047857', '#a7f3d0'],
            'completed' => ['#ecfdf5', '#047857', '#a7f3d0'],
            'rejected' => ['#fef2f2', '#b91c1c', '#fecaca'],
            'failed' => ['#fef2f2', '#b91c1c', '#fecaca'],
        ];

        $paymentStyles = [
            'paid' => ['#ecfdf5', '#047857', '#a7f3d0'],
            'pending' => ['#eff6ff', '#1d4ed8', '#bfdbfe'],
            'challenge' => ['#fef2f2', '#b91c1c', '#fecaca'],
            'failed' => ['#fef2f2', '#b91c1c', '#fecaca'],
        ];

        $filters = $this->filters(request());

        return DataTables::of($this->mobileServiceRequestAdminService->query($filters))
            ->addColumn('requester', function ($request) {
                return '<div class="small service-request-cell" style="line-height: 1.35;">
                    <div class="fw-semibold text-dark service-request-clamp-1">' . e($request->user?->name ?? '-') . '</div>
                    <div class="text-muted service-request-clamp-1" style="max-width: 220px;">' . e($request->user?->email ?? $request->user?->phone ?? '-') . '</div>
                </div>';
            })
            ->addColumn('service', function ($request) {
                $secondary = ($request->request_flow_type ?? 'standard') === 'event_project'
                    ? ($request->eventProjectType?->name . ' / ' . $request->eventProjectNeed?->name)
                    : ($request->needType?->name ?? '-');

                return '<div class="small service-request-cell" style="line-height: 1.35;">
                    <div class="fw-semibold text-dark service-request-clamp-1">' . e($request->service?->title ?? '-') . '</div>
                    <div class="text-muted service-request-clamp-1">' . e(trim($secondary, ' /') ?: '-') . '</div>
                </div>';
            })
            ->addColumn('schedule', function ($request) {
                return '<div class="small service-request-cell" style="line-height: 1.35; max-width: 320px;">
                    <div class="fw-semibold text-dark service-request-clamp-1">' . e(optional($request->survey_date)?->format('d M Y') ?? '-') . '</div>
                    <div class="text-muted service-request-clamp-2" style="max-width: 320px;">' . e($request->survey_address ?? '-') . '</div>
                </div>';
            })
            ->addColumn('region', function ($request) {
                $surveyRegion = $request->survey_region ?? data_get($request->draft_payload, 'surveyRegion');

                return '<div class="small text-muted service-request-cell service-request-clamp-2" style="max-width: 260px; line-height: 1.35;" title="' . e($this->formatRegion($surveyRegion)) . '">' . e($this->formatRegion($surveyRegion)) . '</div>';
            })
            ->addColumn('status_badge', function ($request) {
                $statusStyles = [
                    'draft' => ['#e2e8f0', '#334155', '#cbd5e1'],
                    'waiting_payment' => ['#eff6ff', '#1d4ed8', '#bfdbfe'],
                    'waiting_transfer' => ['#fff7ed', '#c2410c', '#fdba74'],
                    'payment_challenge' => ['#fef2f2', '#b91c1c', '#fecaca'],
                    'approved' => ['#ecfdf5', '#047857', '#a7f3d0'],
                    'completed' => ['#ecfdf5', '#047857', '#a7f3d0'],
                    'rejected' => ['#fef2f2', '#b91c1c', '#fecaca'],
                    'failed' => ['#fef2f2', '#b91c1c', '#fecaca'],
                ];

                [$background, $color, $border] = $statusStyles[$request->status] ?? ['#f1f5f9', '#334155', '#e2e8f0'];
                $label = str_replace('_', ' ', ucfirst($request->status));

                return '<span class="d-inline-flex align-items-center rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px; line-height: 1.2; background: ' . e($background) . '; color: ' . e($color) . '; border: 1px solid ' . e($border) . ';">' . e($label) . '</span>';
            })
            ->addColumn('payment_badge', function ($request) {
                $paymentStyles = [
                    'paid' => ['#ecfdf5', '#047857', '#a7f3d0'],
                    'pending' => ['#eff6ff', '#1d4ed8', '#bfdbfe'],
                    'challenge' => ['#fef2f2', '#b91c1c', '#fecaca'],
                    'failed' => ['#fef2f2', '#b91c1c', '#fecaca'],
                ];

                [$background, $color, $border] = $paymentStyles[$request->payment_status] ?? ['#f1f5f9', '#334155', '#e2e8f0'];
                $label = str_replace('_', ' ', ucfirst($request->payment_status));

                return '<span class="d-inline-flex align-items-center rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px; line-height: 1.2; background: ' . e($background) . '; color: ' . e($color) . '; border: 1px solid ' . e($border) . ';">' . e($label) . '</span>';
            })
            ->addColumn('amount', fn ($request) => 'Rp' . number_format((int) $request->total_amount, 0, ',', '.'))
            ->addColumn('action', function ($request) {
                return '
                    <div class="d-flex justify-content-center align-items-center" style="gap: 10px">
                        <a href="' . route('admin.mobile.service_requests.show', $request->id) . '" class="btn btn-success btn-xs" title="Detail">
                            <i class="ri-eye-line"></i>
                        </a>
                        <a href="' . route('admin.mobile.service_requests.download', $request->id) . '" class="btn btn-primary btn-xs" title="Download PDF">
                            <i class="ri-download-2-line"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['requester', 'service', 'schedule', 'region', 'status_badge', 'payment_badge', 'action'])
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $filename = 'pengajuan-mobile-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new MobileServiceRequestsExport($this->mobileServiceRequestAdminService, $this->filters($request)),
            $filename
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);
        $records = $this->mobileServiceRequestAdminService->query($filters)
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.pdf.mobile-service-requests-list', [
            'records' => $records,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape')->setOption('isRemoteEnabled', false);

        $filename = 'pengajuan-mobile-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function show(int $id)
    {
        $serviceRequest = $this->mobileServiceRequestAdminService->findOrFail($id);

        return $this->view('service-requests.show', [
            'serviceRequest' => $serviceRequest,
            'sections' => $this->sections(),
        ]);
    }

    public function download(int $id)
    {
        $serviceRequest = $this->mobileServiceRequestAdminService->findOrFail($id);
        $pdf = $this->mobileServiceRequestPdfService->generate($serviceRequest);

        $filename = 'proposal-pengajuan-' . $serviceRequest->transaction_code_label . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function photo(string $file)
    {
        $file = basename($file);

        $candidates = [
            public_path('assets/images/mobile/service-requests/' . $file),
            public_path('assets/images/mobile/service-requests/' . Str::of($file)->afterLast('/')->toString()),
            storage_path('app/public/mobile/service-requests/' . $file),
            storage_path('app/public/mobile/service-requests/' . Str::of($file)->afterLast('/')->toString()),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return response()->file($candidate);
            }
        }

        $storageCandidate = 'mobile/service-requests/' . $file;
        if (Storage::disk('public')->exists($storageCandidate)) {
            return response()->file(Storage::disk('public')->path($storageCandidate));
        }

        abort(404);
    }

    protected function filters(Request $request): array
    {
        return [
            'search' => (string) $request->input('search', ''),
            'service_id' => (int) $request->input('service_id', 0),
            'status' => (string) $request->input('status', ''),
            'payment_status' => (string) $request->input('payment_status', ''),
            'survey_from' => (string) $request->input('survey_from', ''),
            'survey_to' => (string) $request->input('survey_to', ''),
            'region' => (string) $request->input('region', ''),
            'province' => (string) $request->input('province', ''),
            'regency' => (string) $request->input('regency', ''),
            'district' => (string) $request->input('district', ''),
            'village' => (string) $request->input('village', ''),
        ];
    }

    protected function formatRegion($surveyRegion): string
    {
        if (! is_array($surveyRegion)) {
            return '-';
        }

        $parts = [
            data_get($surveyRegion, 'village.name'),
            data_get($surveyRegion, 'district.name'),
            data_get($surveyRegion, 'regency.name'),
            data_get($surveyRegion, 'province.name'),
        ];

        $filtered = array_values(array_filter($parts, fn ($part) => is_string($part) && trim($part) !== ''));

        return $filtered !== [] ? implode(', ', $filtered) : '-';
    }

    public function approve(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:5000',
        ]);

        try {
            $serviceRequest = $this->mobileServiceRequestAdminService->approve($id, $request->user(), $validated['admin_note'] ?? null);

            return redirect()
                ->route('admin.mobile.service_requests.show', $serviceRequest->id)
                ->with('success', 'Pengajuan berhasil di-approve.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function complete(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:5000',
        ]);

        try {
            $serviceRequest = $this->mobileServiceRequestAdminService->complete($id, $request->user(), $validated['admin_note'] ?? null);

            return redirect()
                ->route('admin.mobile.service_requests.show', $serviceRequest->id)
                ->with('success', 'Pengajuan berhasil ditandai selesai.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,completed,rejected',
            'admin_note' => 'nullable|string|max:5000',
        ]);

        try {
            $serviceRequest = $this->mobileServiceRequestAdminService->updateStatus(
                $id,
                $request->user(),
                $validated['status'],
                $validated['admin_note'] ?? null
            );

            return redirect()
                ->route('admin.mobile.service_requests.show', $serviceRequest->id)
                ->with('success', 'Status pengajuan berhasil diperbarui.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:5000',
            'admin_note' => 'nullable|string|max:5000',
        ]);

        try {
            $serviceRequest = $this->mobileServiceRequestAdminService->reject(
                $id,
                $request->user(),
                $validated['rejection_reason'],
                $validated['admin_note'] ?? null
            );

            return redirect()
                ->route('admin.mobile.service_requests.show', $serviceRequest->id)
                ->with('success', 'Pengajuan berhasil di-reject.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function chatUser(int $id)
    {
        $serviceRequest = $this->mobileServiceRequestAdminService->findOrFail($id);

        return redirect()->route('admin.mobile.live_chat', [
            'user_id' => $serviceRequest->mobile_user_id,
            'service_request_id' => $serviceRequest->id,
        ]);
    }

    private function sections(): array
    {
        return [
            [
                'title' => 'Overview',
                'route' => route('admin.mobile.index'),
                'icon' => 'ri-dashboard-line',
                'description' => 'Ringkasan kesiapan backoffice mobile.',
            ],
            [
                'title' => 'Users',
                'route' => route('admin.mobile.users'),
                'icon' => 'ri-user-settings-line',
                'description' => 'Kelola customer aplikasi mobile.',
            ],
            [
                'title' => 'OTP Logs',
                'route' => route('admin.mobile.otp_logs'),
                'icon' => 'ri-shield-keyhole-line',
                'description' => 'Pantau OTP email dan SMS.',
            ],
            [
                'title' => 'Service Requests',
                'route' => route('admin.mobile.service_requests.index'),
                'icon' => 'ri-file-list-3-line',
                'description' => 'Review pengajuan dari aplikasi mobile.',
            ],
            [
                'title' => 'Services',
                'route' => route('admin.mobile.services'),
                'icon' => 'ri-service-line',
                'description' => 'Layanan kontraktor untuk mobile ordering.',
            ],
            [
                'title' => 'Need Types',
                'route' => route('admin.mobile.service_need_types'),
                'icon' => 'ri-list-check-3',
                'description' => 'Master jenis kebutuhan layanan per service.',
            ],
            [
                'title' => 'Budget Options',
                'route' => route('admin.mobile.budget_options'),
                'icon' => 'ri-money-dollar-circle-line',
                'description' => 'Master pilihan perkiraan anggaran pengajuan.',
            ],
            [
                'title' => 'Event Projects',
                'route' => route('admin.mobile.event_projects'),
                'icon' => 'ri-calendar-event-line',
                'description' => 'Master jenis project, kebutuhan, paket, dan anggaran event.',
            ],
            [
                'title' => 'Banners',
                'route' => route('admin.mobile.banners'),
                'icon' => 'ri-image-edit-line',
                'description' => 'Banner dan promo home mobile.',
            ],
            [
                'title' => 'Furniture',
                'route' => route('admin.mobile.furniture'),
                'icon' => 'ri-sofa-line',
                'description' => 'Produk furniture dan add-on proyek.',
            ],
            [
                'title' => 'Home Layout',
                'route' => route('admin.mobile.home_layout'),
                'icon' => 'ri-layout-grid-line',
                'description' => 'Pengaturan tema dan susunan home.',
            ],
            [
                'title' => 'Notifications',
                'route' => route('admin.mobile.notifications'),
                'icon' => 'ri-notification-3-line',
                'description' => 'Push notification dan campaign.',
            ],
            [
                'title' => 'Live Chat',
                'route' => route('admin.mobile.live_chat'),
                'icon' => 'ri-message-3-line',
                'description' => 'Percakapan user dengan admin.',
            ],
            [
                'title' => 'Settings',
                'route' => route('admin.mobile.settings'),
                'icon' => 'ri-settings-3-line',
                'description' => 'Biaya survey, pajak, dan metode pembayaran.',
            ],
        ];
    }
}
