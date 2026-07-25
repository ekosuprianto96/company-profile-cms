<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    protected $fillable = [
        'proposal_number',
        'mobile_user_id',
        'mobile_service_id',
        'form_id',
        'status',
        'answers',
        'form_snapshot',
        'price_items',
        'total_amount',
        'admin_note',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'form_snapshot' => 'array',
            'price_items' => 'array',
            'total_amount' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MobileService::class, 'mobile_service_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** Field dari snapshot (fallback ke form aktif bila snapshot kosong). */
    public function snapshotFields(): array
    {
        return $this->form_snapshot['fields'] ?? [];
    }

    public function serviceRequest()
    {
        return $this->hasOne(MobileServiceRequest::class, 'proposal_id');
    }
}
