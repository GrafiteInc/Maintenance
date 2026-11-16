<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Parsers\ComposerOutdated;

class PhpParseOutdated extends AppCommand
{
    protected $signature = 'maintenance:php-outdated {--direct : Only show direct dependencies}';

    protected $description = 'List outdated composer packages';

    public function handle(Shell $shell): int
    {
        $args = ['outdated', '--format=json', '--no-interaction', '--no-ansi'];

        if ($this->option('direct')) {
            $args[] = '--direct';
        }

        $result = $shell->run('composer', $args, cwd: base_path());
        $packages = ComposerOutdated::parse($result->output);

        if (is_null($packages)) {
            $this->warn($result->error);
            $this->error("composer outdated failed with exit code {$result->exitCode}");

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
