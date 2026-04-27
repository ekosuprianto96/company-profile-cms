<?php

namespace App\Repositories;

use App\Models\MobileUserOtp;

class MobileUserOtpRepository extends BaseRepositori
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

    public function __construct()
    {
        $this->setModel(MobileUserOtp::class);
        parent::__construct();
    }

    public function latestPending(string $recipient, string $channel, string $purpose): ?MobileUserOtp
    {
        return $this->model
            ->where('recipient', $recipient)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->whereIn('status', ['pending', 'sent'])
            ->latest()
            ->first();
    }

    public function expirePending(string $recipient, string $channel, string $purpose): void
    {
        $this->model
            ->where('recipient', $recipient)
            ->where('channel', $channel)
            ->where('purpose', $purpose)
            ->whereIn('status', ['pending', 'sent'])
            ->update(['status' => 'expired']);
    }

    public function store(array $attributes): MobileUserOtp
    {
        return $this->model->create($attributes);
    }
}
