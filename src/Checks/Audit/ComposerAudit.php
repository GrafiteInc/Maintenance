<?php

namespace Grafite\Maintenance\Checks\Audit;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Support\Format;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class ComposerAudit extends BaseCheck
{
    public const SEVERITIES = ['critical', 'high', 'medium', 'low'];

    public function run(Shell $shell): CheckResult
    {
        if (! file_exists(base_path('composer.lock'))) {
            return CheckResult::unknown('No composer.lock to audit');
        }

        if (! $shell->which('composer')) {
            return CheckResult::unknown('composer is not available');
        }

        // composer audit exits non-zero when it finds advisories, so read the JSON regardless.
        $data = $shell->run('composer', ['audit', '--format=json', '--locked', '--no-interaction', '--no-ansi'], cwd: base_path())->json();

        if (is_null($data) || ! array_key_exists('advisories', $data)) {
            return CheckResult::unknown('composer audit failed (requires Composer 2.4+)');
        }

        $counts = array_fill_keys(self::SEVERITIES, 0);
        $counts['unrated'] = 0;
        $advisories = [];

        foreach ((array) $data['advisories'] as $package => $packageAdvisories) {
            foreach ((array) $packageAdvisories as $advisory) {
                $severity = strtolower((string) ($advisory['severity'] ?? ''));
                $severity = array_key_exists($severity, $counts) ? $severity : 'unrated';
                $counts[$severity]++;

                $reference = $advisory['cve'] ?? $advisory['advisoryId'] ?? null;
                $advisories[] = "{$package}: " . ($advisory['title'] ?? 'Advisory')
                    . ' (' . implode(', ', array_filter([$reference, $severity])) . ')';
            }
        }

        $abandoned = array_keys(array_filter((array) ($data['abandoned'] ?? []), fn ($value) => $value !== false));
        $total = array_sum($counts);

        $status = match (true) {
            $counts['critical'] + $counts['high'] > 0 => Status::Critical,
            $total > 0 => Status::Warning,
            default => Status::Ok,
        };

        $details = [];

        foreach ($counts as $severity => $count) {
            if ($count > 0) {
                $details[$severity] = Format::count($count, 'advisory');
            }
        }

        if (! empty($advisories)) {
            $details['advisories'] = Format::limit($advisories);
        }

        if (! empty($abandoned)) {
            $details['abandoned_packages'] = Format::limit($abandoned);
        }

        $summary = $total === 0
            ? 'No known vulnerabilities in composer packages'
            : Format::count($total, 'composer advisory') . ' (' . $this->breakdown($counts) . ')';

        if (! empty($abandoned)) {
            $summary .= ', ' . Format::count(count($abandoned), 'abandoned package');
        }

        return new CheckResult($status, $summary, $details);
    }

    protected function breakdown(array $counts): string
    {
        return implode(', ', array_map(
            fn ($severity) => "{$counts[$severity]} {$severity}",
            array_keys(array_filter($counts))
        ));
    }
}
