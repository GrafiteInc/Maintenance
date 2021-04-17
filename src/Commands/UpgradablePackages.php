<?php

namespace Grafite\Maintenance\Commands;

use mikehaertl\shellcommand\Command;
use Illuminate\Console\Command as AppCommand;

class UpgradablePackages extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:upgradable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a count of upgradable server packages.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $command = new Command("apt list --upgradable");

        if ($command->execute()) {
            $data = explode("\n", $command->getOutput());
            $count = count($data) - 1;

            $response = 'Your server has no upgradable packages at this time.';

            if ($count > 0) {
                $response = "Your server has {$count} upgradable packages.";
            }

            $this->info($response);
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        }
    }
}
