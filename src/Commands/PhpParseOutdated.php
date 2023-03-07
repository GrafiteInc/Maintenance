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

        $command = new Command("cd ${path} && /usr/local/bin/composer outdated --format=json");

        if ($command->execute()) {
            $data = collect(json_decode($command->getOutput(), true)['installed']);
            $updateable = [];

            $data->each(function ($item) use (&$updateable) {
                if (in_array($item['latest-status'], ['update-possible', 'semver-safe-update'])) {
                    $updateable[] = '<b>' . $item['name'] . '</b>@' . $item['version'] . ' to ' . $item['latest'];
                }
            });

            $response = 'No packages require updating at this time.';

            if (count($updateable) > 0) {
                $response = 'We recommend updating the following packages:<br><br>' . implode('<br>', $updateable);
            }

            $this->info($response);
        } else {
            $this->warn($command->getError());
            $this->error($command->getExitCode());
        }
    }
}
