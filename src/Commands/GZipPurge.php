<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class GZipPurge extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:gzip-purge {--path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge the gzip files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->option('path');

        if (! $path) {
            $path = storage_path('logs');
        }

        $command = new Command("cd $path && rm *.gz");
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        };
    }
}
