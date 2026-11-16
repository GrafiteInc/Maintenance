<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Concerns\HandlesShellResults;

class LogPurge extends AppCommand
{
    use HandlesShellResults;

    protected $signature = 'maintenance:log-purge';

    protected $description = 'Purge rotated system log files in /var/log';

    public function handle(Shell $shell): int
    {
        return $this->reportResults(
            $shell->run('find', ['/var/log', '-type', 'f', '-regex', '.*\.gz$', '-delete'], sudo: true),
            $shell->run('find', ['/var/log', '-type', 'f', '-regex', '.*\.[0-9]$', '-delete'], sudo: true),
        );
    }
}
