<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile_user_id',
        'service_request_id',
        'assigned_admin_user_id',
        'subject',
        'status',
        'last_message_at',
        'last_message_preview',
        'mobile_last_read_at',
        'admin_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'mobile_last_read_at' => 'datetime',
            'admin_last_read_at' => 'datetime',
        ];
    }

    public function mobileUser(): BelongsTo
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(MobileServiceRequest::class, 'service_request_id');
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_conversation_id');
    }
}
