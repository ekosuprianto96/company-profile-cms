<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeRepository extends Command
{
    protected $signature = 'make:repository {name} {model?}';
    protected $description = 'Generate a new repository class';

    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->argument('model') ?? str_replace('Repository', '', $name);
        $namespace = "App\\Repositories";

        $stubPath = base_path('stubs/repository.stub');
        $targetPath = app_path("Repositories/{$name}.php");

        if (!is_dir(app_path('Repositories'))) {
            mkdir(app_path('Repositories'), 0755, true);
        }

        if (file_exists($targetPath)) {
            $this->error("Repository {$name} already exists!");
            return;
        }

        $stub = file_get_contents($stubPath);
        $content = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}'],
            [$namespace, $name, $model],
            $stub
        );

        file_put_contents($targetPath, $content);
        $this->info("Repository {$name} created successfully!");
    }
}
