<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class SelectIcon implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.select-icon'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
