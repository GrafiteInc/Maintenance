<?php

namespace Grafite\Maintenance\Checks\Health;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class RebootRequired extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        if (! $shell->which('apt-get')) {
            return CheckResult::unknown('Only supported on Debian/Ubuntu');
        }

        if (! $shell->exists('/var/run/reboot-required')) {
            return CheckResult::ok('No reboot pending');
        }

        $packages = array_values(array_unique(array_filter(
            preg_split('/\R/', (string) $shell->read('/var/run/reboot-required.pkgs'))
        )));

        return CheckResult::warning(
            'Reboot required',
            empty($packages) ? [] : ['packages' => Format::limit($packages)]
        );
    }
}
