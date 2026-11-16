<?php

namespace Grafite\Maintenance\Reports;

class SecurityAudit extends ReportRunner
{
    protected function section(): string
    {
        return 'audit';
    }
}
