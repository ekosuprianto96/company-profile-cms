<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:page {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = strtolower(str_replace(' ', '_', $this->argument('name')));
        $stubPath = base_path('stubs/page.stub');
        $targetPath = resource_path("views/admin/pages/{$name}");

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        if (file_exists($targetPath . "/index.blade.php")) {
            $this->error("Page {$name} already exists!");
            return;
        }

        $stub = file_get_contents($stubPath);
        $content = str_replace(
            ['{{ page }}'],
            [$name],
            $stub
        );

        file_put_contents($targetPath . "/index.blade.php", $content);
        $this->info("Page {$name} created successfully!");
    }
}
