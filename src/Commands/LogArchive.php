<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command as AppCommand;

class LogArchive extends AppCommand
{
    protected $signature = 'maintenance:log-archive {--disk=backup : Filesystem disk to archive to}';

    protected $description = 'Archive yesterday\'s log files to a filesystem disk';

    public function handle(): int
    {
        $disk = (string) $this->option('disk');

        if (is_null(config("filesystems.disks.{$disk}"))) {
            $this->error("The [{$disk}] filesystem disk is not configured.");

            return self::FAILURE;
        }

        $date = now()->subDay()->format('Y-m-d');

        $files = [
            'scheduler.log' => "logs/scheduler-{$date}.log",
            "laravel-{$date}.log" => "logs/laravel-{$date}.log",
        ];

        foreach ($files as $file => $destination) {
            $path = storage_path("logs/{$file}");

            if (! is_file($path)) {
                continue;
            }

            $this->info("Archiving {$file}");

            $stream = fopen($path, 'r');
            Storage::disk($disk)->put($destination, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->info('Archived logs');

        return self::SUCCESS;
    }
}
