<?php

namespace App\Traits;

use Closure;

trait FrontView
{
    protected $models;
    protected $path_view = 'frontend.pages.';

    public function setView(string $path)
    {
        $this->path_view = $path;
        return $this;
    }

    public function view(string $path, $param = [])
    {
        return view($this->path_view . ".{$path}", $param);
    }

    public function registerModels(array $models)
    {
        $this->models = $models;
    }

    public function model(string $name)
    {
        return app($this->models[$name]);
    }
}
