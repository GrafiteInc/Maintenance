<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class JournalVacuum extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:journal-vacuum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the journal files.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $command = 'journalctl --vacuum-time=2d';
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
