<?php

namespace App\Forms;

use Closure;
use App\Forms\URL;
use App\Forms\ListSelection;
use App\Forms\SelectInformasi;

class InputField
{
    public function __construct(
        public string $type = 'text',
        public array $attributes = [],
        protected ?Closure $callback = null
    ) {}

    public function render()
    {
        if (is_callable($this->callback)) {
            call_user_func($this->callback, $this);
        }

        return match ($this->type) {
            'text' => (new Text())->render($this->attributes),
            'number' => (new Number())->render($this->attributes),
            'select' => (new Select())->render($this->attributes),
            'textarea' => (new TextArea())->render($this->attributes),
            'list-selection' => (new ListSelection())->render($this->attributes),
            'url' => (new URL())->render($this->attributes),
            'select-icon' => (new SelectIcon())->render($this->attributes),
            'select-informasi' => (new SelectInformasi())->render($this->attributes),
            'text-editor' => (new TextEditor())->render($this->attributes),
            'image' => (new Image())->render($this->attributes),
            'color' => (new Color())->render($this->attributes),
            default => (new Text())->render($this->attributes)
        };
    }

    public function setCollection($collection)
    {
        $this->attributes['collections'] = $collection;
        return $this;
    }

    public function model()
    {
        return app($this->attributes['model']);
    }
}
