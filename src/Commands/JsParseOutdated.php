<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Parsers\NpmOutdated;
use Illuminate\Console\Command as AppCommand;

class JsParseOutdated extends AppCommand
{
    protected $signature = 'maintenance:js-outdated';

    protected $description = 'List outdated npm packages';

    public function handle(Shell $shell): int
    {
        // npm outdated exits 1 when anything is outdated, so parse the output regardless.
        $result = $shell->run('npm', ['outdated', '--json'], cwd: base_path());
        $packages = NpmOutdated::parse($result->output);

        if (is_null($packages)) {
            $this->warn($result->error);
            $this->error("npm outdated failed with exit code {$result->exitCode}");

            return self::FAILURE;
        }

        if (empty($packages)) {
            $this->info('No packages require updating at this time.');

            return self::SUCCESS;
        }

        $this->info('We recommend updating the following packages:');
        $this->table(['Package', 'Current', 'Latest'], array_map(
            fn ($package) => [$package['name'], $package['current'], $package['latest']],
            $packages
        ));

        return self::SUCCESS;
    }
}
