<?php

namespace App\Facades;

use App\Services\PageService;
use Illuminate\Support\Facades\Facade;

class PageFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'pageService';
    }
}
