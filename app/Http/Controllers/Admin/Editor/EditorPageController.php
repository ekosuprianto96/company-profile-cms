<?php

namespace App\Http\Controllers\Admin\Editor;

use App\Http\Controllers\Controller;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class EditorPageController extends Controller
{
    use AdminView;

    public function __construct()
    {
        $this->setView('admin.pages.editor.');
    }
}
