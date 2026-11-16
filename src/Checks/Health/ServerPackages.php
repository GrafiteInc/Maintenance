<?php

namespace Grafite\Maintenance\Checks\Health;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;
use Grafite\Maintenance\Parsers\AptUpgradable;

class ServerPackages extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        if (! $shell->which('apt-get')) {
            return CheckResult::unknown('Only supported on Debian/Ubuntu (apt)');
        }

        $result = $shell->run('apt', ['list', '--upgradable']);

        if ($result->failed()) {
            return CheckResult::unknown('apt list failed: ' . ($result->error ?: "exit code {$result->exitCode}"));
        }

        $upgradable = AptUpgradable::parse($result->output);

        if ($upgradable['total'] === 0) {
            return CheckResult::ok('All server packages are up to date');
        }

        $details = [
            'upgradable' => Format::count($upgradable['total'], 'package'),
            'security' => Format::count($upgradable['security'], 'package'),
        ];

        $security = array_filter($upgradable['packages'], fn ($package) => $package['security']);

        if (! empty($security)) {
            $details['security_updates'] = Format::limit(array_map(
                fn ($package) => trim("{$package['name']} {$package['from']} → {$package['to']}"),
                $security
            ));

            return CheckResult::warning(
                Format::count($upgradable['security'], 'security update') . ' pending (' . $upgradable['total'] . ' upgradable)',
                $details
            );
        }

        return CheckResult::ok(Format::count($upgradable['total'], 'package') . ' upgradable, no security updates', $details);
    }
}
