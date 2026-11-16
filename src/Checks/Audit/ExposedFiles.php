<?php

namespace Grafite\Maintenance\Checks\Audit;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class ExposedFiles extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        $status = Status::Ok;
        $problems = [];
        $details = [];

        foreach ((array) config('maintenance.audit.exposed_paths', []) as $path) {
            if (file_exists(public_path($path))) {
                $status = Status::Critical;
                $problems[] = "public/{$path}";
            }
        }

        if (! empty($problems)) {
            $details['public_files'] = $problems;
        }

        $env = app()->environmentFilePath();

        if (is_file($env) && PHP_OS_FAMILY !== 'Windows') {
            $mode = fileperms($env) & 0777;
            $details['env_permissions'] = sprintf('%04o', $mode);

            if ($mode & 0002) {
                $status = Status::Critical;
                $problems[] = basename($env) . ' is world-writable';
            } elseif ($mode & 0004) {
                $status = $status->worse(Status::Warning);
                $problems[] = basename($env) . ' is world-readable';
            }
        }

        $summary = empty($problems)
            ? 'No sensitive files exposed'
            : 'Exposed: ' . implode(', ', $problems);

        return new CheckResult($status, $summary, $details);
    }
}
