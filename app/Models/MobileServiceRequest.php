<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MobileServiceRequest extends Model
{
    use HasFactory;

    protected $appends = [
        'transaction_code_label',
    ];

    protected $fillable = [
        'mobile_user_id',
        'mobile_service_id',
        'proposal_id',
        'request_flow_type',
        'transaction_code',
        'building_key',
        'building_label',
        'description',
        'issue_photos',
        'survey_address',
        'survey_address_detail',
        'survey_region',
        'survey_latitude',
        'survey_longitude',
        'survey_date',
        'survey_fee',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'products_amount',
        'voucher_id',
        'discount_amount',
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
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'payment_payload',
        'draft_payload',
        'steps_snapshot',
        'drafted_at',
        'payment_method_selected_at',
        'paid_at',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'handled_by_user_id',
        'admin_note',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'issue_photos' => 'array',
            'payment_payload' => 'array',
            'draft_payload' => 'array',
            'steps_snapshot' => 'array',
            'survey_region' => 'array',
            'survey_date' => 'date',
            'drafted_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'payment_method_selected_at' => 'datetime',
            'payment_proof_uploaded_at' => 'datetime',
            'paid_at' => 'datetime',
            'survey_fee' => 'integer',
            'tax_percentage' => 'integer',
            'tax_amount' => 'integer',
            'total_amount' => 'integer',
            'products_amount' => 'integer',
        ];
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceRequestProduct::class);
    }

    /**
     * Pengajuan hanya bisa dibatalkan user selama BELUM bayar biaya survey dan
     * BELUM diproses admin (belum di-review/approve/reject) serta masih di tahap awal.
     */
    public function canBeCancelled(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        if ($this->payment_status === 'paid' || $this->paid_at) {
            return false;
        }

        if ($this->reviewed_at || $this->approved_at || $this->rejected_at) {
            return false;
        }

        return in_array($this->status, ['draft', 'waiting_payment', 'waiting_transfer', 'payment_challenge', 'pending'], true);
    }

    public function getTransactionCodeLabelAttribute(): string
    {
        if (! empty($this->transaction_code)) {
            return (string) $this->transaction_code;
        }

        if (! empty($this->id)) {
            return sprintf('SR-EK%05d', (int) $this->id);
        }

        return 'SR-EK00000';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MobileService::class, 'mobile_service_id');
    }


    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }

    public function proposal()
    {
        return $this->belongsTo(\App\Models\Proposal::class, 'proposal_id');
    }
}
