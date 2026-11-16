<?php

namespace Grafite\Maintenance\Reports;

use Throwable;
use Illuminate\Support\Str;
use Grafite\Maintenance\Checks\Check;
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Checks\CheckResult;

abstract class ReportRunner
{
    /**
     * The config section under `maintenance.` ("report" or "audit").
     */
    abstract protected function section(): string;

    public function __construct(protected Shell $shell)
    {
    }

    public function run(): Report
    {
        $section = 'maintenance.' . $this->section();
        $results = [];

        foreach ($this->checks() as $check) {
            try {
                $results[$check->key()] = $check->run($this->shell);
            } catch (Throwable $exception) {
                $results[$check->key()] = CheckResult::unknown(
                    'Check failed: ' . Str::limit($exception->getMessage(), 200)
                );
            }
        }

        return new Report(
            title: (string) config("{$section}.title"),
            tag: (string) config("{$section}.tag"),
            results: $results,
            host: (string) (config('maintenance.host') ?: gethostname() ?: 'unknown host'),
            environment: (string) app()->environment(),
            generatedAt: now()->toDateTimeString(),
            appendStatusToTag: (bool) config('maintenance.append_status_to_tag', true),
        );
    }

    /**
     * @return array<int, Check>
     */
    public function checks(): array
    {
        return array_map(
            fn ($check) => is_string($check) ? app($check) : $check,
            array_values((array) config('maintenance.' . $this->section() . '.checks', []))
        );
    }
}
