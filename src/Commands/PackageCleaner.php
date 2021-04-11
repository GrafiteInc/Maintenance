<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class PackageCleaner extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:package-cleaner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean the packages';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $command = 'apt-get autoremove && apt-get autoclean';
        $password = env('SUDO_PW');

        $command = new Command("echo '$password' | sudo -S -k $command");
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        };
    }
}
