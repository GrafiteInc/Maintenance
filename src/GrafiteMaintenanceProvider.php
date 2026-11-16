<?php

namespace Grafite\Maintenance;

use Grafite\Maintenance\Commands\Live;
use Grafite\Maintenance\Commands\Audit;
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Commands\Report;
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
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/maintenance.php' => config_path('maintenance.php'),
            ], 'maintenance-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/maintenance.php', 'maintenance');

        $this->app->singleton(Shell::class);

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
            Report::class,
            Audit::class,
        ]);
    }
}
