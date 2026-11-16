<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Parsers\AptUpgradable;

class UpgradablePackages extends AppCommand
{
    protected $signature = 'maintenance:upgradable';

    protected $description = 'Get a count of upgradable server packages.';

    public function handle(Shell $shell): int
    {
        $result = $shell->run('apt', ['list', '--upgradable']);

        if ($result->failed()) {
            $this->warn($result->error);
            $this->error("apt list failed with exit code {$result->exitCode}");

            return self::FAILURE;
        }

        $upgradable = AptUpgradable::parse($result->output);

        if ($upgradable['total'] === 0) {
            $this->info('Your server has no upgradable packages at this time.');

            return self::SUCCESS;
        }

        $this->info("Your server has {$upgradable['total']} upgradable packages ({$upgradable['security']} security).");

        return self::SUCCESS;
    }
}
