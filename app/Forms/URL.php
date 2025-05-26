<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class URL implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.url'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
