<?php

namespace App\Services;

use App\Mail\MobileServiceRequestDecisionMail;
use App\Mail\MobileServiceRequestSubmittedMail;
use App\Models\MobileServiceRequest;
use App\Models\User;
use App\Repositories\MobileServiceRequestRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MobileServiceRequestAdminService
{
    public function __construct(
        protected MobileServiceRequestRepository $mobileServiceRequestRepository,
        protected SystemNotificationService $systemNotificationService
    ) {}

    public function query(array $filters = []): Builder
    {
        return $this->applyFilters($this->mobileServiceRequestRepository->adminQuery(), $filters);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function (Builder $builder) use ($like) {
                $builder
                    ->where('transaction_code', 'like', $like)
                    ->orWhere('building_label', 'like', $like)
                    ->orWhere('survey_address', 'like', $like)
                    ->orWhereHas('user', function (Builder $userQuery) use ($like) {
                        $userQuery
                            ->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    })
                    ->orWhereHas('service', function (Builder $serviceQuery) use ($like) {
                        $serviceQuery->where('title', 'like', $like);
                    })
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(survey_region, JSON_OBJECT()), '$.province.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(survey_region, JSON_OBJECT()), '$.regency.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(survey_region, JSON_OBJECT()), '$.district.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(survey_region, JSON_OBJECT()), '$.village.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.province.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.regency.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.district.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.village.name')) LIKE ?", [$like]);
            });
        }

        foreach (['status', 'payment_status'] as $field) {
            $value = trim((string) ($filters[$field] ?? ''));

            if ($value !== '') {
                $query->where($field, $value);
            }
        }

        $serviceId = (int) ($filters['service_id'] ?? 0);
        if ($serviceId > 0) {
            $query->where('mobile_service_id', $serviceId);
        }

        $surveyFrom = trim((string) ($filters['survey_from'] ?? ''));
        if ($surveyFrom !== '') {
            $query->whereDate('survey_date', '>=', $surveyFrom);
        }

        $surveyTo = trim((string) ($filters['survey_to'] ?? ''));
        if ($surveyTo !== '') {
            $query->whereDate('survey_date', '<=', $surveyTo);
        }

        $regionSearch = trim((string) ($filters['region'] ?? ''));
        if ($regionSearch !== '') {
            $like = '%' . $regionSearch . '%';

            $query->where(function (Builder $builder) use ($like) {
                $builder
                    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.province.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.regency.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.district.name')) LIKE ?", [$like])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.village.name')) LIKE ?", [$like]);
            });
        }

        foreach ([
            'province' => 'province.name',
            'regency' => 'regency.name',
            'district' => 'district.name',
            'village' => 'village.name',
        ] as $filterKey => $jsonPath) {
            $value = trim((string) ($filters[$filterKey] ?? ''));

            if ($value === '') {
                continue;
            }

            $like = '%' . $value . '%';

                $query->where(function (Builder $builder) use ($like, $jsonPath) {
                    $builder
                        ->whereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(COALESCE(survey_region, JSON_OBJECT()), '$.{$jsonPath}')) LIKE ?",
                            [$like]
                        )
                        ->orWhereRaw(
                            "JSON_UNQUOTE(JSON_EXTRACT(COALESCE(draft_payload, JSON_OBJECT()), '$.surveyRegion.{$jsonPath}')) LIKE ?",
                            [$like]
                        );
                });
            }

        return $query;
    }

    public function findOrFail(int $id): MobileServiceRequest
    {
        $serviceRequest = $this->mobileServiceRequestRepository->findForAdmin($id);

        if (! $serviceRequest) {
            throw new \Exception('Pengajuan mobile tidak ditemukan.', 404);
        }

        return $serviceRequest;
    }

    public function approve(int $id, User $admin, ?string $note = null): MobileServiceRequest
    {
        return $this->updateStatus($id, $admin, 'approved', $note);
    }

    public function complete(int $id, User $admin, ?string $note = null): MobileServiceRequest
    {
        return $this->updateStatus($id, $admin, 'completed', $note);
    }

    public function reject(int $id, User $admin, string $reason, ?string $note = null): MobileServiceRequest
    {
        return $this->updateStatus($id, $admin, 'rejected', $note ?? $reason, $reason);
    }

    public function updateStatus(int $id, User $admin, string $status, ?string $note = null, ?string $rejectionReason = null): MobileServiceRequest
    {
        if (! in_array($status, ['approved', 'completed', 'rejected'], true)) {
            throw new \Exception('Status pengajuan tidak valid.', 422);
        }

        $serviceRequest = DB::transaction(function () use ($id, $admin, $status, $note, $rejectionReason) {
            $serviceRequest = $this->findOrFail($id);

            $updatePayload = [
                'status' => $status,
                'reviewed_at' => now(),
                'handled_by_user_id' => $admin->id,
                'admin_note' => $note,
            ];

            if ($status === 'approved') {
                $updatePayload['approved_at'] = now();
                $updatePayload['rejected_at'] = null;
                $updatePayload['rejection_reason'] = null;
            }

            if ($status === 'completed') {
                $updatePayload['rejected_at'] = null;
                $updatePayload['rejection_reason'] = null;
            }

            if ($status === 'rejected') {
                $updatePayload['rejected_at'] = now();
                $updatePayload['approved_at'] = null;
                $updatePayload['rejection_reason'] = $rejectionReason ?: $note ?: 'Ditolak oleh admin.';
            }

            $serviceRequest->update($updatePayload);

            return $serviceRequest->fresh(['user', 'service', 'needType', 'budgetOption', 'handledBy']);
        });

        $this->notifyDecision($serviceRequest, $status, $status === 'rejected' ? ($rejectionReason ?: $note) : $note);
        $this->systemNotificationService->notifyServiceRequestDecision($serviceRequest, $status);

        return $serviceRequest;
    }

    public function notifySubmitted(MobileServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->user?->email) {
            Mail::to($serviceRequest->user->email)
                ->queue(new MobileServiceRequestSubmittedMail($serviceRequest->fresh(['user', 'service'])));
        }

        $adminEmail = config('mail.from.address');

        if ($adminEmail) {
            Mail::to($adminEmail)
                ->queue(new MobileServiceRequestSubmittedMail($serviceRequest->fresh(['user', 'service']), 'admin'));
        }
    }

    public function notifyDecision(MobileServiceRequest $serviceRequest, string $decision, ?string $note = null): void
    {
        if ($serviceRequest->user?->email) {
            Mail::to($serviceRequest->user->email)
                ->queue(new MobileServiceRequestDecisionMail($serviceRequest->fresh(['user', 'service']), $decision, $note));
        }

        $adminEmail = config('mail.from.address');

        if ($adminEmail) {
            Mail::to($adminEmail)
                ->queue(new MobileServiceRequestDecisionMail($serviceRequest->fresh(['user', 'service']), $decision, $note));
        }
    }

    public function stats(): array
    {
        return [
            [
                'label' => 'Total Pengajuan',
                'value' => $this->mobileServiceRequestRepository->count(),
                'icon' => 'ri-file-list-3-line',
                'tone' => 'primary',
            ],
            [
                'label' => 'Draft / Menunggu',
                'value' => $this->mobileServiceRequestRepository
                    ->whereIn('status', ['draft', 'waiting_payment', 'waiting_transfer', 'payment_challenge'])
                    ->count(),
                'icon' => 'ri-timer-line',
                'tone' => 'warning',
            ],
            [
                'label' => 'Selesai / Lunas',
                'value' => $this->mobileServiceRequestRepository
                    ->where('payment_status', 'paid')
                    ->count(),
                'icon' => 'ri-checkbox-circle-line',
                'tone' => 'success',
            ],
            [
                'label' => 'Approved',
                'value' => $this->mobileServiceRequestRepository
                    ->where('status', 'approved')
                    ->count(),
                'icon' => 'ri-thumb-up-line',
                'tone' => 'info',
            ],
        ];
    }
}
