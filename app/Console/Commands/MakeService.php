<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Generate a new service class';

    public function handle()
    {
        $name = $this->argument('name');
        $namespace = "App\\Services";

        $stubPath = base_path('stubs/service.stub');
        $targetPath = app_path("Services/{$name}.php");

        if (!is_dir(app_path('Services'))) {
            mkdir(app_path('Services'), 0755, true);
        }

        if (file_exists($targetPath)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        $stub = file_get_contents($stubPath);
        $content = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $name],
            $stub
        );

        file_put_contents($targetPath, $content);
        $this->info("Service {$name} created successfully!");
    }
}
