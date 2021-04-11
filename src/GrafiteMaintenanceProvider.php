<?php

namespace Grafite\Maintenance;

use Illuminate\Support\ServiceProvider;
use Grafite\Maintenance\Commands\LogPurge;
use Grafite\Maintenance\Commands\GZipPurge;
use Grafite\Maintenance\Commands\JournalVacuum;
use Grafite\Maintenance\Commands\PackageCleaner;

class GrafiteMaintenanceProvider extends ServiceProvider
{
    /**
     * Boot method.
     */
    public function boot()
    {
        // do nothing
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */
        $this->commands([
            GZipPurge::class,
            LogPurge::class,
            JournalVacuum::class,
            PackageCleaner::class,
        ]);
    }
}
