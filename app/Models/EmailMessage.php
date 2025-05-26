<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessage extends Model
{
    protected $guarded = ['id'];

    public static function booted()
    {

        static::deleting(function ($message) {
            $message->inBoundmail()->delete();
        });
    }

    public function inBoundmail()
    {
        return $this->hasMany(InboundEmail::class, 'message_id', 'message_id');
    }
}
