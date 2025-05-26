<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class SelectInformasi implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.select-informasi'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
