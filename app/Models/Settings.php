<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use Blameable;

    protected $guarded = ['id'];
}
