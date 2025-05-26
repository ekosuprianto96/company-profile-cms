<?php

namespace App\Http\Controllers\Admin\Modules;

enum Status
{
    case AKTIF;
    case NONAKTIF;
    case PUBLISH;
    case UNPUBLISH;

    public function value()
    {
        return match ($this) {
            Status::AKTIF => 1,
            Status::NONAKTIF => 0,
            Status::PUBLISH => 1,
            Status::UNPUBLISH => 0
        };
    }

    public function text()
    {
        return match ($this) {
            Status::AKTIF => 'Aktif',
            Status::NONAKTIF => 'Tidak Aktif',
            Status::PUBLISH => 'Publish',
            Status::UNPUBLISH => 'Unpublish'
        };
    }
}
