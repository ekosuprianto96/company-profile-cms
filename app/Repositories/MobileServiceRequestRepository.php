<?php

namespace App\Repositories;

use App\Models\MobileServiceRequest;
use Illuminate\Database\Eloquent\Collection;

class MobileServiceRequestRepository extends BaseRepositori
{
    protected $fillable = [
        'mobile_user_id',
        'mobile_service_id',
        'request_flow_type',
        'transaction_code',
        'building_key',
        'building_label',
        'description',
        'issue_photos',
        'survey_address',
        'survey_region',
        'survey_latitude',
        'survey_longitude',
        'survey_date',
        'survey_fee',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_gateway_provider',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'midtrans_transaction_status',
        'midtrans_payment_type',
        'payment_reference',
        'payment_payload',
        'draft_payload',
        'drafted_at',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'handled_by_user_id',
        'admin_note',
        'rejection_reason',
        'payment_method_selected_at',
        'paid_at',
    ];

    public function __construct()
    {
        $this->setModel(MobileServiceRequest::class);
        parent::__construct();
    }

    public function findLatestByUser(int $userId): ?MobileServiceRequest
    {
        return $this->model
            ->where('mobile_user_id', $userId)
            ->latest('id')
            ->first();
    }

    public function findByUserAndId(int $userId, int $id): ?MobileServiceRequest
    {
        return $this->model
            ->where('mobile_user_id', $userId)
            ->where('id', $id)
            ->first();
    }

    public function findByMidtransOrderId(string $orderId): ?MobileServiceRequest
    {
        return $this->model
            ->where('midtrans_order_id', $orderId)
            ->orWhere('payment_reference', $orderId)
            ->first();
    }

    public function listByUser(int $userId): Collection
    {
        return $this->model
            ->where('mobile_user_id', $userId)
            ->latest('id')
            ->limit(100) // batasi riwayat per-user
            ->get();
    }

    public function adminQuery()
    {
        return $this->model
            ->with(['user', 'service', 'handledBy'])
            ->latest('id');
    }

    public function findForAdmin(int $id): ?MobileServiceRequest
    {
        return $this->model
            ->with(['user', 'service', 'handledBy'])
            ->find($id);
    }

    public function store(array $attributes): MobileServiceRequest
    {
        return $this->model->create($attributes);
    }
}
