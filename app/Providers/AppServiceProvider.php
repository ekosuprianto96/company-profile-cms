<?php

namespace App\Providers;

use App\Models\MobileUserOtp;
use App\Observers\MobileUserOtpObserver;
use App\Services\PageService;
use App\Services\MobileAppSettingService;
use Illuminate\Support\Carbon;
use App\Rules\ExistEmailInformasi;
use App\Rules\ExistPhoneInformasi;
use App\Services\InformasiService;
use App\Services\SocialMediaService;
use Illuminate\Support\Facades\URL;
use App\Services\EmailSettingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rules\Email;
use Illuminate\Support\ServiceProvider;
use App\Repositories\InformasiRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\SocialMediaRepository;
use App\Repositories\EmailSettingRepository;
use Mockery\Generator\StringManipulation\Pass\Pass;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('pageService', function () {
            return new PageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        MobileUserOtp::observe(MobileUserOtpObserver::class);

        Validator::extend('exists_email', function ($attribute, $value, $parameters, $validator) {
            return (new ExistEmailInformasi())->validate($attribute, $value, function () {});
        });

        Validator::extend('exists_phone', function ($attribute, $value, $parameters, $validator) {
            return (new ExistPhoneInformasi())->validate($attribute, $value, function () {});
        });

        // Pemuatan pengaturan dari DB. Dibungkus try/catch supaya boot TIDAK crash
        // ketika tabel belum ada (mis. `php artisan migrate` di DB bersih / fresh
        // deploy) atau row pengaturan belum di-seed. Bila gagal, app memakai nilai
        // default dari config/*.php + .env.
        try {
            $settings = cache()->rememberForever('app_settings', function () {
                return (new InformasiService(new InformasiRepository))
                    ->findByKey('settings')
                    ->decode(true, false)
                    ->get();
            });

            $footer = cache()->rememberForever('footer_settings', function () {
                return (new InformasiService(new InformasiRepository))
                    ->findByKey('footer')
                    ->decode(true, false)
                    ->get();
            });

            $settingsEmail = cache()->rememberForever('email_config', function () {
                return (new EmailSettingService(new EmailSettingRepository))
                    ->getAllSettings();
            });

            $socialMedia = cache()->rememberForever('social_media', function () {
                return (new SocialMediaService(new SocialMediaRepository))
                    ->getAll(function ($collection) {
                        return $collection->where('an', 1);
                    });
            });

            $mobileAppSettings = cache()->rememberForever('mobile_app_settings_bootstrap', function () {
                return (new MobileAppSettingService(
                    new \App\Repositories\MobileAppSettingRepository()
                ))->getSettings();
            });

            config([
                'app.name' => $settings->value['app_name'] ?? config('app.name'),
                'app.timezone' => $settings->value['timezone'] ?? 'Asia/Jakarta',
                'app.logo' => $settings->value['app_logo'] ?? '',
                'mobile_request.survey_fee' => $mobileAppSettings['survey_fee'] ?? config('mobile_request.survey_fee'),
                'mobile_request.tax_percentage' => $mobileAppSettings['tax_percentage'] ?? 0,
                'invoice.templates.service' => $mobileAppSettings['invoice_template_service'] ?? config('invoice.templates.service'),
                'invoice.templates.product' => $mobileAppSettings['invoice_template_product'] ?? config('invoice.templates.product'),
            ]);

            config()->set('settings', $settings);
            config()->set('social_media', $socialMedia);
            config()->set('footer_settings', $footer);

            // set config mail
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
        } catch (\Throwable $e) {
            // DB/pengaturan belum siap — lanjut dengan default. Jangan gagalkan boot.
        }
    }
}
