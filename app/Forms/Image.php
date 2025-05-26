<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class Image implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.image-upload'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }
}
