<?php

namespace Grafite\Maintenance\Commands;

use Illuminate\Console\Command;
use Grafite\Maintenance\Reports\SecurityAudit;
use Grafite\Maintenance\Concerns\RendersReport;

class Audit extends Command
{
    use RendersReport;

    protected $signature = 'maintenance:audit
        {--format=table : Output format: table, json or mission-control}';

    protected $description = 'Audit dependencies and application config for security issues';

    public function handle(SecurityAudit $audit): int
    {
        return $this->renderReport($audit->run());
    }
}
