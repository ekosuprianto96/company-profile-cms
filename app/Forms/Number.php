<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class Number implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.number'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
