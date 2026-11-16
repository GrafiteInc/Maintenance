<?php

namespace Grafite\Maintenance\Reports;

use Illuminate\Support\Str;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Checks\CheckResult;

/**
 * The result of a health report or security audit.
 *
 * toMissionControl() returns named arguments for grafite/mission-control-laravel:
 *
 *     mission_control_notify(...$report->toMissionControl());
 */
final class Report
{
    /**
     * @param  array<string, CheckResult>  $results  keyed by check key
     */
    public function __construct(
        public readonly string $title,
        public readonly string $tag,
        public readonly array $results,
        public readonly string $host,
        public readonly string $environment,
        public readonly string $generatedAt,
        public readonly bool $appendStatusToTag = true,
    ) {
    }

    /**
     * The worst status across the checks that ran. Unknown checks only decide
     * the status when nothing else could run.
     */
    public function status(): Status
    {
        $known = array_filter(
            array_map(fn (CheckResult $result) => $result->status, $this->results),
            fn (Status $status) => $status !== Status::Unknown
        );

        if (empty($known)) {
            return empty($this->results) ? Status::Ok : Status::Unknown;
        }

        return Status::worst($known);
    }

    public function failed(): bool
    {
        return $this->status() === Status::Critical;
    }

    /**
     * @return array<string, int> counts per status value
     */
    public function counts(): array
    {
        $counts = array_fill_keys(array_map(fn (Status $status) => $status->value, Status::cases()), 0);

        foreach ($this->results as $result) {
            $counts[$result->status->value]++;
        }

        return $counts;
    }

    public function summary(): string
    {
        $counts = $this->counts();

        $parts = [
            $counts['critical'] . ' critical',
            $counts['warning'] . ' ' . Str::plural('warning', $counts['warning']),
            $counts['ok'] . ' ok',
        ];

        if ($counts['unknown'] > 0) {
            $parts[] = $counts['unknown'] . ' unknown';
        }

        return implode(', ', $parts);
    }

    public function fullTitle(): string
    {
        return Str::limit("{$this->title}: " . strtoupper($this->status()->value) . " ({$this->host})", 255, '');
    }

    public function fullTag(): string
    {
        return $this->appendStatusToTag ? "{$this->tag}:{$this->status()->value}" : $this->tag;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status()->value,
            'host' => $this->host,
            'environment' => $this->environment,
            'generated_at' => $this->generatedAt,
            'summary' => $this->summary(),
            'checks' => array_map(fn (CheckResult $result) => $result->toArray(), $this->results),
        ];
    }

    public function toJson(int $flags = 0): string
    {
        return json_encode(
            $this->toArray(),
            $flags | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return array{title: string, tag: string, message: string}
     */
    public function toMissionControl(): array
    {
        return [
            'title' => $this->fullTitle(),
            'tag' => $this->fullTag(),
            'message' => $this->toJson(),
        ];
    }
}
