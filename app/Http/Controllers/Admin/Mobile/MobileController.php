<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Services\MobileUserAdminService;
use App\Services\MobileAppSettingService;
use App\Services\SystemNotificationService;
use App\Traits\AdminView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class MobileController extends Controller
{
    use AdminView;

    public function __construct(
        protected MobileUserAdminService $mobileUserAdminService,
        protected SystemNotificationService $systemNotificationService
    )
    {
        $this->setView('admin.pages.mobile');
    }

    public function index()
    {
        return $this->view('index', [
            'stats' => $this->mobileUserAdminService->stats(),
            'sections' => $this->sections(),
        ]);
    }

    public function users()
    {
        return $this->view('users', [
            'sections' => $this->sections(),
        ]);
    }

    public function usersData()
    {
        return DataTables::of($this->mobileUserAdminService->userQuery())
            ->addColumn('verification', function ($user) {
                $badges = [];

                $badges[] = '<span class="badge badge-sm badge-' . ($user->email_verified_at ? 'success' : 'secondary') . '">Email ' . ($user->email_verified_at ? 'Verified' : 'Pending') . '</span>';
                $badges[] = '<span class="badge badge-sm badge-' . ($user->phone_verified_at ? 'success' : 'secondary') . '">Phone ' . ($user->phone_verified_at ? 'Verified' : 'Pending') . '</span>';

                return implode(' ', $badges);
            })
            ->addColumn('status', function ($user) {
                return '<span class="badge badge-sm badge-' . ($user->is_active ? 'success' : 'danger') . '">' . ($user->is_active ? 'Aktif' : 'Nonaktif') . '</span>';
            })
            ->addColumn('contacts', function ($user) {
                $email = $user->email ?: '-';
                $phone = $user->phone ?: '-';

                return '<div class="small"><div><strong>Email:</strong> ' . e($email) . '</div><div><strong>Phone:</strong> ' . e($phone) . '</div></div>';
            })
            ->addColumn('activity', function ($user) {
                return '<div class="small"><div><strong>Tokens:</strong> ' . ($user->tokens_count ?? 0) . '</div><div><strong>Last login:</strong> ' . ($user->last_login_at?->format('d M Y H:i') ?? '-') . '</div></div>';
            })
            ->addColumn('registered_at', fn ($user) => $user->created_at?->format('d M Y H:i'))
            ->addColumn('action', function ($user) {
                return '
                    <div class="d-flex justify-content-center align-items-center" style="gap: 10px">
                        <button type="button" onclick="toggleStatus(' . $user->id . ')" class="btn btn-' . ($user->is_active ? 'warning' : 'success') . ' btn-xs" title="Toggle Status">
                            <i class="ri-' . ($user->is_active ? 'pause-circle-line' : 'play-circle-line') . '"></i>
                        </button>
                        <button type="button" onclick="revokeTokens(' . $user->id . ')" class="btn btn-danger btn-xs" title="Revoke Tokens">
                            <i class="ri-key-line"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['verification', 'status', 'contacts', 'activity', 'action'])
            ->make(true);
    }

    public function toggleUserStatus(int $id)
    {
        try {
            $user = $this->mobileUserAdminService->toggleStatus($id);

            return response()->json([
                'status' => true,
                'message' => 'Status user berhasil diubah menjadi ' . ($user->is_active ? 'aktif' : 'nonaktif') . '.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function revokeUserTokens(int $id)
    {
        try {
            $this->mobileUserAdminService->revokeTokens($id);

            return response()->json([
                'status' => true,
                'message' => 'Semua token user berhasil dihapus.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function otpLogs()
    {
        return $this->view('otps', [
            'sections' => $this->sections(),
        ]);
    }

    public function otpLogsData()
    {
        return DataTables::of($this->mobileUserAdminService->otpQuery())
            ->addColumn('user', function ($otp) {
                return '<div class="small"><div><strong>' . e($otp->user->name ?? '-') . '</strong></div><div>' . e($otp->user->email ?? $otp->user->phone ?? '-') . '</div></div>';
            })
            ->addColumn('otp_code', function ($otp) {
                if ($otp->channel !== 'email' || empty($otp->code_encrypted)) {
                    return '<span class="text-muted">-</span>';
                }

                try {
                    $code = Crypt::decryptString((string) $otp->code_encrypted);
                } catch (\Throwable) {
                    $code = '-';
                }

                return '<div class="d-flex align-items-center" style="gap: 8px;">
                    <span class="badge badge-sm badge-dark">' . e($code) . '</span>
                    <button type="button" class="btn btn-light btn-xs" onclick="navigator.clipboard.writeText(\'' . e($code) . '\')">
                        <i class="ri-file-copy-line"></i>
                    </button>
                </div>';
            })
            ->addColumn('channel_badge', function ($otp) {
                $color = $otp->channel === 'sms' ? 'info' : 'primary';

                return '<span class="badge badge-sm badge-' . $color . '">' . strtoupper($otp->channel) . '</span>';
            })
            ->addColumn('status_badge', function ($otp) {
                $color = match ($otp->status) {
                    'verified' => 'success',
                    'expired' => 'secondary',
                    'sent' => 'info',
                    'pending' => 'warning',
                    default => 'danger',
                };

                return '<span class="badge badge-sm badge-' . $color . '">' . ucfirst($otp->status) . '</span>';
            })
            ->addColumn('timing', function ($otp) {
                return '<div class="small">
                    <div><strong>Sent:</strong> ' . ($otp->sent_at?->format('d M Y H:i') ?? '-') . '</div>
                    <div><strong>Expires:</strong> ' . ($otp->expires_at?->format('d M Y H:i') ?? '-') . '</div>
                    <div><strong>Verified:</strong> ' . ($otp->verified_at?->format('d M Y H:i') ?? '-') . '</div>
                </div>';
            })
            ->rawColumns(['user', 'otp_code', 'channel_badge', 'status_badge', 'timing'])
            ->make(true);
    }

    public function services()
    {
        return $this->sectionView(
            'Layanan Mobile',
            'Gunakan data layanan existing sebagai titik awal, lalu tambahkan field yang lebih cocok untuk aplikasi order jasa seperti harga mulai, kategori, durasi estimasi, dan CTA pemesanan.',
            [
                'Pisahkan layanan website company profile dan layanan order mobile bila kebutuhan kontennya berbeda.',
                'Tambahkan atribut mobile seperti cover, galeri, harga awal, badge promo, dan langkah kerja.',
                'Siapkan endpoint API khusus mobile setelah struktur data layanan stabil.',
            ]
        );
    }

    public function banners()
    {
        return $this->sectionView(
            'Banner Mobile',
            'Banner untuk aplikasi mobile sebaiknya dipisah dari banner website karena rasio, copywriting, dan target aksinya biasanya berbeda.',
            [
                'Tambahkan posisi banner seperti home hero, promo strip, dan featured campaign.',
                'Simpan action type seperti buka detail layanan, buka produk, atau deep link tertentu.',
                'Sediakan penjadwalan publish agar promo musiman bisa otomatis aktif/nonaktif.',
            ]
        );
    }

    public function furniture()
    {
        return $this->sectionView(
            'Produk Furniture',
            'Modul ini cocok dipakai untuk katalog furniture atau add-on proyek seperti kitchen set, sofa, meja, dan paket interior.',
            [
                'Buat tabel produk dengan SKU, harga, stok opsional, thumbnail, dan galeri.',
                'Tambahkan kategori produk dan relasi ke layanan bila produk menjadi upsell dari jasa kontraktor.',
                'Pertimbangkan status preorder, custom order, dan lead time produksi.',
            ]
        );
    }

    public function homeLayout()
    {
        return $this->sectionView(
            'Home Layout Mobile',
            'Home aplikasi mobile butuh panel pengaturan yang lebih fleksibel daripada website, karena susunan blok, banner, kategori, dan rekomendasi akan sering berubah.',
            [
                'Simpan konfigurasi blok home dalam JSON agar urutan section bisa diatur dari admin.',
                'Pisahkan setting warna, copy CTA, featured services, dan featured products.',
                'Kalau ingin sangat fleksibel, buat builder sederhana berbasis section aktif/nonaktif dan urutan tampil.',
            ]
        );
    }

    public function notifications()
    {
        $filters = [
            'type' => request()->string('type')->trim()->toString(),
            'read_status' => request()->string('read_status')->trim()->toString(),
            'search' => request()->string('search')->trim()->toString(),
        ];
        $perPage = max((int) request()->integer('per_page', 10), 5);
        $notifications = $this->notificationQuery($filters)
            ->paginate($perPage)
            ->withQueryString();

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'table' => view('admin.pages.mobile.notifications-partials.table', [
                    'notifications' => $notifications,
                ])->render(),
                'pagination' => view('admin.pages.mobile.notifications-partials.pagination', [
                    'notifications' => $notifications,
                    'filters' => $filters,
                    'perPage' => $perPage,
                ])->render(),
                'summary' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                    'total' => $notifications->total(),
                ],
                'unread_count' => request()->user()?->unreadNotifications()->count() ?? 0,
            ]);
        }

        return $this->view('notifications', [
            'sections' => $this->sections(),
            'filters' => $filters,
            'notifications' => $notifications,
            'unreadCount' => request()->user()?->unreadNotifications()->count() ?? 0,
            'perPage' => $perPage,
        ]);
    }

    private function notificationQuery(array $filters)
    {
        $query = request()->user()?->notifications()->latest();

        if (! $query) {
            abort(403);
        }

        if (($filters['type'] ?? '') !== '' && in_array($filters['type'], ['promo', 'informasi', 'konfirmasi'], true)) {
            $query->where('data->type', $filters['type']);
        }

        if (($filters['read_status'] ?? '') === 'read') {
            $query->whereNotNull('read_at');
        } elseif (($filters['read_status'] ?? '') === 'unread') {
            $query->whereNull('read_at');
        }

        if (($filters['search'] ?? '') !== '') {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('data->title', 'like', '%' . $search . '%')
                    ->orWhere('data->message', 'like', '%' . $search . '%')
                    ->orWhere('data->url', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public function sendNotificationForm()
    {
        return $this->view('notifications-send', [
            'sections' => $this->sections(),
            'notificationUsers' => $this->mobileUserAdminService->userQuery()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone']),
        ]);
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:all,specific',
            'user_ids' => 'required_if:target,specific|array',
            'user_ids.*' => 'integer|exists:mobile_users,id',
            'type' => 'required|in:promo,informasi,konfirmasi',
            'title' => 'required|string|max:120',
            'message' => 'required|string|max:20000',
            'url' => 'nullable|string|max:500',
        ]);

        try {
            $this->systemNotificationService->notifyCampaign(
                $validated['title'],
                $validated['message'],
                $validated['type'],
                $validated['target'] === 'all',
                $validated['user_ids'] ?? [],
                $validated['url'] ?? null,
                [],
                $validated['message']
            );

            return redirect()
                ->back()
                ->with('success', 'Notifikasi berhasil dikirim.');
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }
    }

    public function liveChat()
    {
        return $this->sectionView(
            'Live Chat',
            'Live chat butuh keputusan arsitektur sejak awal karena menyentuh real-time delivery, assignment admin, histori chat, dan notifikasi.',
            [
                'Pilih apakah chat akan dibangun internal atau integrasi ke provider pihak ketiga.',
                'Kalau internal, siapkan tabel conversation, messages, attachments, dan read status.',
                'Gunakan websocket atau polling bertahap sesuai kebutuhan performa dan budget infrastruktur.',
            ]
        );
    }

    public function settings(MobileAppSettingService $mobileAppSettingService)
    {
        return $this->view('settings', [
            'sections' => $this->sections(),
            'settings' => $mobileAppSettingService->getSettings(),
            'surveyCoverage' => $mobileAppSettingService->surveyCoverage(),
            'manualTransfers' => $mobileAppSettingService->manualTransfers(),
        ]);
    }

    public function regionsProvinces()
    {
        return response()->json([
            'status' => true,
            'data' => $this->fetchRegionData('/provinces.json'),
        ]);
    }

    public function regionsRegencies(Request $request)
    {
        $validated = $request->validate([
            'province_code' => 'required|string|max:50',
        ]);

        return response()->json([
            'status' => true,
            'data' => $this->fetchRegionData('/regencies/' . $validated['province_code'] . '.json'),
        ]);
    }

    public function regionsDistricts(Request $request)
    {
        $validated = $request->validate([
            'regency_code' => 'required|string|max:50',
        ]);

        return response()->json([
            'status' => true,
            'data' => $this->fetchRegionData('/districts/' . $validated['regency_code'] . '.json'),
        ]);
    }

    public function regionsVillages(Request $request)
    {
        $validated = $request->validate([
            'district_code' => 'required|string|max:50',
        ]);

        return response()->json([
            'status' => true,
            'data' => $this->fetchRegionData('/villages/' . $validated['district_code'] . '.json'),
        ]);
    }

    public function updateSettings(Request $request, MobileAppSettingService $mobileAppSettingService)
    {
        $validated = $request->validate([
            'survey_fee' => 'required|integer|min:0',
            'tax_percentage' => 'required|integer|min:0|max:100',
            'payment_gateway_enabled' => 'nullable|boolean',
            'payment_gateway_provider' => 'nullable|string|max:50',
            'survey_coverage_enabled' => 'nullable|boolean',
            'survey_coverage_whatsapp_number' => 'nullable|string|max:30',
            'survey_coverage_whatsapp_message' => 'nullable|string|max:2000',
            'survey_coverage_rules' => 'nullable|array',
            'survey_coverage_rules.*.id' => 'nullable|string|max:100',
            'survey_coverage_rules.*.area_name' => 'nullable|string|max:150',
            'survey_coverage_rules.*.province_code' => 'nullable|string|max:50',
            'survey_coverage_rules.*.province_name' => 'nullable|string|max:150',
            'survey_coverage_rules.*.regency_code' => 'nullable|string|max:50',
            'survey_coverage_rules.*.regency_name' => 'nullable|string|max:150',
            'survey_coverage_rules.*.sort_order' => 'nullable|integer|min:0',
            'survey_coverage_rules.*.is_active' => 'nullable|boolean',
            'manual_transfers' => 'nullable|array',
            'manual_transfers.*.id' => 'nullable|string|max:100',
            'manual_transfers.*.bank_name' => 'nullable|string|max:100',
            'manual_transfers.*.account_name' => 'nullable|string|max:255',
            'manual_transfers.*.account_number' => 'nullable|string|max:100',
            'manual_transfers.*.notes' => 'nullable|string|max:2000',
            'manual_transfers.*.sort_order' => 'nullable|integer|min:0',
            'manual_transfers.*.is_active' => 'nullable|boolean',
        ]);

        $manualTransfers = collect($validated['manual_transfers'] ?? [])
            ->map(function ($transfer, $index) {
                if (! is_array($transfer)) {
                    return null;
                }

                $bankName = trim((string) ($transfer['bank_name'] ?? ''));
                $accountName = trim((string) ($transfer['account_name'] ?? ''));
                $accountNumber = trim((string) ($transfer['account_number'] ?? ''));

                if ($bankName === '' && $accountName === '' && $accountNumber === '') {
                    return null;
                }

                return [
                    'id' => $transfer['id'] ?? 'manual-transfer-' . ($index + 1),
                    'bank_name' => $bankName ?: 'BCA',
                    'account_name' => $accountName ?: '-',
                    'account_number' => $accountNumber ?: '-',
                    'notes' => (string) ($transfer['notes'] ?? ''),
                    'sort_order' => (int) ($transfer['sort_order'] ?? ($index + 1)),
                    'is_active' => filter_var($transfer['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $surveyCoverageRules = collect($validated['survey_coverage_rules'] ?? [])
            ->map(function ($rule, $index) {
                if (! is_array($rule)) {
                    return null;
                }

                $areaName = trim((string) ($rule['area_name'] ?? ''));
                $provinceCode = trim((string) ($rule['province_code'] ?? ''));
                $provinceName = trim((string) ($rule['province_name'] ?? ''));
                $regencyCode = trim((string) ($rule['regency_code'] ?? ''));
                $regencyName = trim((string) ($rule['regency_name'] ?? ''));

                if ($areaName === '' && $provinceCode === '' && $regencyCode === '') {
                    return null;
                }

                return [
                    'id' => $rule['id'] ?? 'survey-coverage-' . ($index + 1),
                    'area_name' => $areaName,
                    'province' => [
                        'code' => $provinceCode,
                        'name' => $provinceName,
                    ],
                    'regency' => [
                        'code' => $regencyCode,
                        'name' => $regencyName,
                    ],
                    'sort_order' => (int) ($rule['sort_order'] ?? ($index + 1)),
                    'is_active' => filter_var($rule['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter()
            ->values()
            ->all();

        $mobileAppSettingService->update([
            'survey_fee' => (int) $validated['survey_fee'],
            'tax_percentage' => (int) $validated['tax_percentage'],
            'payment_gateway' => [
                'enabled' => $request->boolean('payment_gateway_enabled'),
                'provider' => $validated['payment_gateway_provider'] ?: 'midtrans',
            ],
            'survey_coverage' => [
                'enabled' => $request->boolean('survey_coverage_enabled'),
                'whatsapp_number' => trim((string) ($validated['survey_coverage_whatsapp_number'] ?? '')),
                'whatsapp_message' => trim((string) ($validated['survey_coverage_whatsapp_message'] ?? '')),
                'rules' => $surveyCoverageRules,
            ],
            'manual_transfers' => $manualTransfers,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Pengaturan mobile berhasil disimpan.');
    }

    private function sectionView(string $title, string $description, array $recommendations)
    {
        return $this->view('section', [
            'title' => $title,
            'description' => $description,
            'recommendations' => $recommendations,
            'sections' => $this->sections(),
        ]);
    }

    private function fetchRegionData(string $path): array
    {
        return cache()->remember('mobile_regions:' . $path, now()->addDay(), function () use ($path) {
            $response = Http::timeout(15)
                ->retry(2, 200)
                ->get('https://wilayah.id/api' . $path);

            if (! $response->successful()) {
                return [];
            }

            return collect(data_get($response->json(), 'data', []))
                ->filter(fn ($item) => is_array($item) && isset($item['code'], $item['name']))
                ->values()
                ->map(fn ($item) => [
                    'code' => (string) $item['code'],
                    'name' => (string) $item['name'],
                ])
                ->all();
        });
    }

    public function sections(): array
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
                'title' => 'Inspire',
                'route' => route('admin.mobile.inspirasi.index'),
                'icon' => 'ri-article-line',
                'description' => 'Konten inspirasi dan insight visual untuk mobile app.',
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
