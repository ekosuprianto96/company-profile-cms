<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class Color implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.color'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
