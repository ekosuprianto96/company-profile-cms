<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class Text implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.text'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
