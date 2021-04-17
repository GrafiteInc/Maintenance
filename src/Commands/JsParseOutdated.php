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
        $path = base_path();

        $command = new Command("cd ${path} && npm outdated --json=true");

        if ($command->execute()) {
            $data = collect(json_decode($command->getOutput(), true));
            $updateable = [];

            $data->each(function ($details, $item) use (&$updateable) {
                $updateable[] = '<b>' . $item . '</b>@' . $details['current'] . ' to ' . $details['latest'];
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
