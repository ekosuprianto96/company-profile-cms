<?php

namespace App\Jobs;

use App\Mail\SendBulkEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\EmailSettingService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Repositories\EmailSettingRepository;

class SendBulkEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?string $email = null,
        public mixed $data = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $settingsEmail = cache()->rememberForever('email_config', function () {
                return (new EmailSettingService(new EmailSettingRepository))
                    ->getAllSettings();
            });


            // set config mail dari database
            setConfigMail(
                transport: 'smtp',
                host: $settingsEmail['host'] ?? '',
                port: $settingsEmail['port'] ?? 0,
                username: $settingsEmail['username'] ?? '',
                password: $settingsEmail['password'] ?? '',
                encryption: 'tls',
                fromAddress: $settingsEmail['from_email'] ?? '',
                fromName: config('app.name')
            );

            // Paksa reload Mailer agar config baru terbaca
            app()->forgetInstance(\Illuminate\Mail\Mailer::class);
            app()->forgetInstance(\Illuminate\Mail\MailManager::class);
            app()->forgetInstance('mailer');

            // kirim email
            Mail::to($this->email)->send(new SendBulkEmail($this->data));


            $this->data->update([
                'status' => 1
            ]);
        } catch (\Throwable $e) {

            $this->data->update([
                'status' => 2
            ]);

            throw $e;
        }
    }

    public function fail($exception = null)
    {
        $this->data->update([
            'status' => 2
        ]);

        Log::error('Error send bulk email : ' . $exception->getTraceAsString());
    }
}
