<?php

namespace Grafite\Maintenance\Reports;

class HealthReport extends ReportRunner
{
    protected function section(): string
    {
        return 'report';
    }
}
