<?php

namespace App\Repositories;

use App\Models\MobileUser;

class MobileUserRepository extends BaseRepositori
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
        'last_login_at',
    ];

    public function __construct()
    {
        $this->setModel(MobileUser::class);
        parent::__construct();
    }

    public function findByEmail(string $email): ?MobileUser
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByPhone(string $phone): ?MobileUser
    {
        return $this->model->where('phone', $phone)->first();
    }

    public function findByLogin(string $login): ?MobileUser
    {
        return $this->model
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();
    }

    public function findByRecipient(string $recipient, string $channel): ?MobileUser
    {
        return $channel === 'sms'
            ? $this->findByPhone($recipient)
            : $this->findByEmail($recipient);
    }

    public function store(array $attributes): MobileUser
    {
        return $this->model->create($attributes);
    }
}
