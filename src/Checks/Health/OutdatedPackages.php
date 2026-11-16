<?php

namespace Grafite\Maintenance\Checks\Health;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;
use Grafite\Maintenance\Parsers\NpmOutdated;
use Grafite\Maintenance\Parsers\ComposerOutdated;

/**
 * Informational: outdated packages never raise the status on their own.
 * Vulnerable packages are flagged by maintenance:audit instead.
 */
class OutdatedPackages extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        $details = [];
        $summary = [];

        foreach (['composer' => $this->composer($shell), 'npm' => $this->npm($shell)] as $manager => $packages) {
            if (is_null($packages)) {
                continue;
            }

            $summary[] = Format::count(count($packages), "{$manager} package");

            if (! empty($packages)) {
                $details[$manager] = Format::limit(array_map(
                    fn ($package) => "{$package['name']} {$package['current']} → {$package['latest']}",
                    $packages
                ));
            }
        }

        if (empty($summary)) {
            return CheckResult::unknown('Neither composer nor npm could be checked');
        }

        return CheckResult::ok(implode(', ', $summary) . ' outdated', $details);
    }

    protected function composer(Shell $shell): ?array
    {
        if (! file_exists(base_path('composer.json')) || ! $shell->which('composer')) {
            return null;
        }

        return ComposerOutdated::parse(
            $shell->run('composer', ['outdated', '--direct', '--format=json', '--no-interaction', '--no-ansi'], cwd: base_path())->output
        );
    }

    protected function npm(Shell $shell): ?array
    {
        if (! file_exists(base_path('package.json')) || ! $shell->which('npm')) {
            return null;
        }

        return NpmOutdated::parse($shell->run('npm', ['outdated', '--json'], cwd: base_path())->output);
    }
}
