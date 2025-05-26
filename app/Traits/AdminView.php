<?php

namespace App\Traits;

trait AdminView
{

    protected $models;
    protected $path_view = 'admin.pages.';

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
