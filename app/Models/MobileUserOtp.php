<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileUserOtp extends Model
{
    protected $fillable = [
        'mobile_user_id',
        'purpose',
        'channel',
        'recipient',
        'provider',
        'provider_sid',
        'code_hash',
        'code_encrypted',
        'provider_response',
        'expires_at',
        'sent_at',
        'verified_at',
        'attempts',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'provider_response' => 'array',
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id');
    }
}
