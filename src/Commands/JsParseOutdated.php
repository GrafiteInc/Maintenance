<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class JsParseOutdated extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:js-outdated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing of outdated JS packages';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = app_path();

        $command = new Command("cd $path && npm outdated --json=true");
        if ($command->execute()) {
            $this->info($command->getOutput());
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        };
    }
}
