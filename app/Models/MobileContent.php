<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MobileContent extends Model
{
    protected $table = 'mobile_contents';

    protected $fillable = [
        'key',
        'title',
        'body',
        'updated_by',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
