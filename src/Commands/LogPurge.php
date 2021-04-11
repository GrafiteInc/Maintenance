<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class LogPurge extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:log-purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge the log files';

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
        $command = 'sudo -S -k find /var/log -type f -regex ".*\.gz$" -delete';
        $command .= '&& sudo -S -k find /var/log -type f -regex ".*\.[0-9]$" -delete';
        $password = env('SUDO_PW');

        $command = new Command("echo '$password' | $command");
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        };
    }
}
