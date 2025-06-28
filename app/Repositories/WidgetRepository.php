<?php

namespace App\Repositories;

use App\Models\Widget;

class WidgetRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        $this->setModel(Widget::class);
        parent::__construct();
    }

    public function getAllWidgets()
    {
        return $this->model->latest()->get();
    }
}
