<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailMessageSending extends Model
{
    protected $table = 'email_message_sending';
    protected $guarded = ['id'];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->send_by = auth()->user()->id;
        });
    }

    public function sendBy()
    {
        return $this->belongsTo(User::class, 'send_by');
    }

    public function contact()
    {
        return $this->belongsTo(CustomerContact::class, 'contact_id');
    }
}
