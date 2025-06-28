<?php

namespace App\Http\Controllers\Admin\Widget;

use App\Http\Controllers\Controller;
use App\Services\WidgetService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    use AdminView;

    public function __construct(
        private WidgetService $widget
    ) {
        $this->setView('admin.pages.widget');
    }

    public function index()
    {
        $widgets = $this->widget->getAllWidgets();
        return $this->view('index', [
            'widgets' => $widgets
        ]);
    }
}
