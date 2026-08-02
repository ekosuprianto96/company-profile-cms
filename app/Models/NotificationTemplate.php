<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_PUSH = 'push';
    public const CHANNEL_IN_APP = 'in_app';

    public const AUDIENCE_USER = 'user';
    public const AUDIENCE_ADMIN = 'admin';

    protected $fillable = [
        'event_key',
        'channel',
        'audience',
        'name',
        'subject',
        'body',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
