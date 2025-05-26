<?php

namespace App\Rules;

use Closure;
use App\Services\InformasiService;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistPhoneInformasi
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail)
    {
        $informasi = app(InformasiService::class)->findByKey('kontak')
            ->decode(false, false)
            ->get();

        return in_array($value, $informasi->value->phones);
    }
}
