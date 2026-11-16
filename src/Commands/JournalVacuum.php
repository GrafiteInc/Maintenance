<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Concerns\HandlesShellResults;

class JournalVacuum extends AppCommand
{
    use HandlesShellResults;

    protected $signature = 'maintenance:journal-vacuum {--time=2d : Keep journal entries newer than this}';

    protected $description = 'Clear the journal files.';

    public function handle(Shell $shell): int
    {
        return $this->reportResults(
            $shell->run('journalctl', ['--vacuum-time=' . $this->option('time')], sudo: true)
        );
    }
}
