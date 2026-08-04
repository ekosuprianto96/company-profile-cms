<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCustomBlock extends Model
{
    protected $fillable = ['name', 'html', 'created_by'];
}
