<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu langkah dalam Template Rules Step.
 *
 * kind:
 * - core     : step wajib bawaan sistem — tidak bisa dihapus, key & trigger terkunci,
 *              nama + keterangan tetap boleh diedit admin.
 * - optional : step yang disediakan sistem, admin bebas memakai atau tidak.
 * - custom   : step buatan admin sendiri.
 *
 * trigger_status: event engine yang otomatis mencentang step ini
 * (created/payment_selected/proof_uploaded/paid/reviewed/approved/completed/
 * rejected/cancelled); null = dicentang manual oleh admin.
 *
 * actions: daftar action terpilih dari katalog sistem (bukan CRUD):
 * notif_inapp / notif_push / notif_email / notif_sms / notif_admin.
 */
class StepTemplateStep extends Model
{
    protected $fillable = [
        'step_template_id',
        'key',
        'name',
        'description',
        'kind',
        'trigger_status',
        'actions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'actions' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(StepTemplate::class, 'step_template_id');
    }
}
