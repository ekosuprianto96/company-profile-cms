<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Content;

/**
 * Pilih view email berdasarkan hasil render template notifikasi:
 * - ada 'html' (desain dari Email Builder) → view 'emails.designed' (HTML lengkap).
 * - selain itu → 'emails.templated' (layout brand bawaan).
 */
trait BuildsTemplatedEmail
{
    protected function templatedContent(array $rendered): Content
    {
        $plain = trim((string) ($rendered['plain'] ?? ''));

        if (! empty($rendered['html'])) {
            return new Content(
                view: 'emails.designed',
                text: $plain !== '' ? 'emails.plaintext' : null,
                with: ['html' => $rendered['html'], 'plain' => $plain],
            );
        }

        return new Content(
            view: 'emails.templated',
            text: $plain !== '' ? 'emails.plaintext' : null,
            with: ['headline' => $rendered['subject'] ?? '', 'body' => $rendered['body'] ?? '', 'plain' => $plain],
        );
    }
}
