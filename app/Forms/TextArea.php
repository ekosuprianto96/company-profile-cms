<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class TextArea implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.text-area'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
