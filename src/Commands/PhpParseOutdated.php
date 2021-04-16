<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class PhpParseOutdated extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:php-outdated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing of outdated PHP packages';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = base_path();

        $command = new Command("cd $path && composer outdated --format=json");
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        };
    }
}
