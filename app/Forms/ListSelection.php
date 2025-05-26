<?php

namespace App\Forms;

use App\Contracts\InputFieldInterface;

class ListSelection implements InputFieldInterface
{
    public function __construct(
        protected $viewPath = 'admin.components.inputs.list-selection'
    ) {}

    public function render($attributes = null)
    {
        return view($this->viewPath, $attributes)->render();
    }

    // public function collections()
}
