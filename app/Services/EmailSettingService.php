<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\EmailSettingRepository;

class EmailSettingService
{
    public function __construct(
        private EmailSettingRepository $emailSetting,
        private ?Request $request = null
    ) {}

    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    public function getAllSettings()
    {
        return $this->emailSetting->getSettings();
    }

    public function update()
    {
        $setiings = $this->emailSetting->updateSettings([
            'host' => $this->request->host,
            'port' => $this->request->port,
            'username' => $this->request->username,
            'password' => $this->request->password,
            'from_email' => $this->request->from_email
        ]);

        cache()->forget('email_config');

        return $setiings;
    }
}
