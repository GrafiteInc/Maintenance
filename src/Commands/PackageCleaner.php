<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Concerns\HandlesShellResults;

class PackageCleaner extends AppCommand
{
    use HandlesShellResults;

    protected $signature = 'maintenance:package-cleaner';

    protected $description = 'Remove unused apt packages and clean the package cache';

    public function handle(Shell $shell): int
    {
        return $this->reportResults(
            $shell->run('apt-get', ['-y', 'autoremove'], sudo: true),
            $shell->run('apt-get', ['-y', 'autoclean'], sudo: true),
        );
    }
}
