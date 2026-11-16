<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Console\Command as AppCommand;

class Live extends AppCommand
{
    protected $signature = 'maintenance:live';

    protected $description = 'Place an application in live mode after updating';

    public function handle(): int
    {
        $backup = public_path('index-bk.php');

        if (! file_exists($backup)) {
            $this->warn('No public/index-bk.php backup found, the application is not in maintenance mode.');

            return self::FAILURE;
        }

        copy($backup, public_path('index.php'));
        unlink($backup);

        $this->info('Application is now in live mode');

        return self::SUCCESS;
    }
}
