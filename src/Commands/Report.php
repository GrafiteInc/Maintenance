<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Console\Command;
use Grafite\Maintenance\Reports\HealthReport;
use Grafite\Maintenance\Concerns\RendersReport;

class Report extends Command
{
    use RendersReport;

    protected $signature = 'maintenance:report
        {--format=table : Output format: table, json or mission-control}';

    protected $description = 'Report on server and application health';

    public function handle(HealthReport $report): int
    {
        return $this->renderReport($report->run());
    }
}
