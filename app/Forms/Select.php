<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class Select implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.select'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
