<?php

namespace Grafite\Maintenance\Commands;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Illuminate\Console\Command as AppCommand;
use Grafite\Maintenance\Concerns\HandlesShellResults;

class GZipPurge extends AppCommand
{
    use HandlesShellResults;

    protected $signature = 'maintenance:gzip-purge {--path= : Directory to purge (defaults to storage/logs)}';

    protected $description = 'Purge the gzip files';

    public function handle(Shell $shell): int
    {
        $path = $this->option('path') ?: storage_path('logs');

        if (! is_dir($path)) {
            $this->error("Directory not found: {$path}");

            return self::FAILURE;
        }

        $result = $shell->run('find', [$path, '-maxdepth', '1', '-type', 'f', '-name', '*.gz', '-print', '-delete']);

        if ($result->failed()) {
            return $this->reportResults($result);
        }

        $deleted = array_filter(explode("\n", $result->output));

        $this->info('Deleted ' . Format::count(count($deleted), 'gzip file') . " from {$path}");

        return self::SUCCESS;
    }
}
