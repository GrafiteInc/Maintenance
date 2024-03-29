<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command as AppCommand;

class LogArchive extends AppCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:log-archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive log files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logs = scandir(base_path('storage/logs'));

        $date = now()->yesterday()->format('Y-m-d');

        if (in_array('scheduler.log', $logs)) {
            $this->info('Archiving scheduler.log');
            Storage::disk('backup')->put("logs/scheduler-$date.log", file_get_contents(base_path('storage/logs/scheduler.log')));
        }

        if (in_array("laravel-$date.log", $logs)) {
            $this->info("Archiving laravel-$date.log");
            Storage::disk('backup')->put("logs/laravel-$date.log", file_get_contents(base_path("storage/logs/laravel-$date.log")));
        }

        $this->info('Archived logs');
    }
}
