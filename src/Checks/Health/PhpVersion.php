<?php

namespace Grafite\Maintenance\Checks\Health;

use Illuminate\Support\Carbon;
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class PhpVersion extends BaseCheck
{
    /**
     * End of security support per minor version (https://www.php.net/supported-versions.php).
     * Override or extend with `maintenance.report.php_eol`.
     */
    public const EOL = [
        '7.4' => '2022-11-28',
        '8.0' => '2023-11-26',
        '8.1' => '2025-12-31',
        '8.2' => '2026-12-31',
        '8.3' => '2027-12-31',
        '8.4' => '2028-12-31',
        '8.5' => '2029-12-31',
    ];

    public function run(Shell $shell): CheckResult
    {
        $version = $this->version();
        $minor = implode('.', array_slice(explode('.', $version), 0, 2));
        $eol = array_merge(self::EOL, (array) config('maintenance.report.php_eol', []))[$minor] ?? null;

        $details = ['version' => $version, 'sapi' => PHP_SAPI];

        if (is_null($eol)) {
            return CheckResult::ok("PHP {$version} (no end of life date known)", $details);
        }

        $eolDate = Carbon::parse($eol)->endOfDay();
        $details['security_support_until'] = $eolDate->toDateString();

        if ($eolDate->isPast()) {
            return CheckResult::critical("PHP {$minor} reached end of life on {$eolDate->toDateString()}", $details);
        }

        $days = (int) now()->diffInDays($eolDate);

        if ($days <= (int) config('maintenance.report.thresholds.php_eol_days', 180)) {
            return CheckResult::warning("PHP {$minor} security support ends in " . Format::count($days, 'day'), $details);
        }

        return CheckResult::ok("PHP {$version}, supported until {$eolDate->toDateString()}", $details);
    }

    protected function version(): string
    {
        return PHP_VERSION;
    }
}
