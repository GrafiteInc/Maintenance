<?php

namespace Grafite\Maintenance;

use Grafite\Maintenance\Commands\Live;
use Illuminate\Support\ServiceProvider;
use Grafite\Maintenance\Commands\LogPurge;
use Grafite\Maintenance\Commands\Updating;
use Grafite\Maintenance\Commands\GZipPurge;
use Grafite\Maintenance\Commands\LogArchive;
use Grafite\Maintenance\Commands\JournalVacuum;
use Grafite\Maintenance\Commands\PackageCleaner;
use Grafite\Maintenance\Commands\JsParseOutdated;
use Grafite\Maintenance\Commands\PhpParseOutdated;
use Grafite\Maintenance\Commands\UpgradablePackages;

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
            Live::class,
            Updating::class,
            GZipPurge::class,
            LogPurge::class,
            LogArchive::class,
            JournalVacuum::class,
            PackageCleaner::class,
            JsParseOutdated::class,
            PhpParseOutdated::class,
            UpgradablePackages::class,
        ]);
    }
}
