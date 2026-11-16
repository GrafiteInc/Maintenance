<?php

namespace Grafite\Maintenance\Checks\Audit;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

/**
 * Keys in .env.example that are missing from .env. Only key names are ever
 * reported, never values.
 */
class EnvDrift extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        $env = app()->environmentFilePath();
        $example = app()->environmentPath() . DIRECTORY_SEPARATOR . '.env.example';

        if (! is_file($example)) {
            return CheckResult::unknown('No .env.example to compare against');
        }

        if (! is_file($env)) {
            return CheckResult::unknown('No ' . basename($env) . ' file (environment may be provided externally)');
        }

        $missing = array_values(array_diff($this->keys($example), $this->keys($env)));

        if (empty($missing)) {
            return CheckResult::ok(basename($env) . ' has every key from .env.example');
        }

        return CheckResult::warning(
            Format::count(count($missing), 'key') . ' from .env.example missing in ' . basename($env),
            ['missing_keys' => Format::limit($missing, 50)]
        );
    }

    protected function keys(string $path): array
    {
        preg_match_all('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_.]*)\s*=/m', (string) file_get_contents($path), $matches);

        return array_unique($matches[1]);
    }
}
