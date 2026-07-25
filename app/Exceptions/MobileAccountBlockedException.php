<?php

namespace App\Exceptions;

use Exception;

/**
 * Dilempar saat user mobile yang diblokir mencoba memperoleh/menggunakan sesi.
 * Ditangani khusus oleh AuthController agar respons memuat code 'account_blocked'
 * sehingga aplikasi mobile bisa mengalihkan ke layar informasi blokir.
 */
class MobileAccountBlockedException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?string $reason = null,
    ) {
        parent::__construct($message, 403);
    }
}
