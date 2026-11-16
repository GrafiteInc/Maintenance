<?php

namespace Grafite\Maintenance\Checks\Audit;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class NpmAudit extends BaseCheck
{
    public const SEVERITIES = ['critical', 'high', 'moderate', 'low', 'info'];

    public function run(Shell $shell): CheckResult
    {
        if (! file_exists(base_path('package-lock.json'))) {
            return CheckResult::unknown('No package-lock.json to audit');
        }

        if (! $shell->which('npm')) {
            return CheckResult::unknown('npm is not available');
        }

        // npm audit exits non-zero when it finds vulnerabilities, so read the JSON regardless.
        $data = $shell->run('npm', ['audit', '--json'], cwd: base_path())->json();
        $metadata = $data['metadata']['vulnerabilities'] ?? null;

        if (! is_array($metadata)) {
            return CheckResult::unknown('npm audit failed' . (isset($data['error']['summary']) ? ': ' . $data['error']['summary'] : ''));
        }

        $counts = [];

        foreach (self::SEVERITIES as $severity) {
            $counts[$severity] = (int) ($metadata[$severity] ?? 0);
        }

        $total = $counts['critical'] + $counts['high'] + $counts['moderate'] + $counts['low'];

        $status = match (true) {
            $counts['critical'] + $counts['high'] > 0 => Status::Critical,
            $total > 0 => Status::Warning,
            default => Status::Ok,
        };

        if ($total === 0) {
            return CheckResult::ok('No known vulnerabilities in npm packages');
        }

        $details = [];

        foreach ($counts as $severity => $count) {
            if ($count > 0) {
                $details[$severity] = Format::count($count, 'vulnerability');
            }
        }

        // npm 7+ lists affected packages under "vulnerabilities".
        $packages = [];

        foreach ((array) ($data['vulnerabilities'] ?? []) as $name => $vulnerability) {
            $packages[] = "{$name} (" . ($vulnerability['severity'] ?? 'unknown') . ')';
        }

        if (! empty($packages)) {
            $details['packages'] = Format::limit($packages);
        }

        $breakdown = implode(', ', array_map(
            fn ($severity) => "{$counts[$severity]} {$severity}",
            array_keys(array_filter(array_diff_key($counts, ['info' => 0])))
        ));

        return new CheckResult($status, Format::count($total, 'npm vulnerability') . " ({$breakdown})", $details);
    }
}
