<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunQueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-que-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command run que command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('queue:work');
    }
}
