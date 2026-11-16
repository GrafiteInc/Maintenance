<?php

namespace Grafite\Maintenance\Checks;

use Grafite\Maintenance\Support\Shell;

interface Check
{
    /**
     * A snake_case key, used as the row label in payloads ("server_packages"
     * renders as "Server Packages" in Mission Control).
     */
    public function key(): string;

    /**
     * Run the check. Return CheckResult::unknown() rather than throwing when the
     * check cannot run on this host.
     */
    public function run(Shell $shell): CheckResult;
}
