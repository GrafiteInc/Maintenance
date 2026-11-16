<?php

namespace Grafite\Maintenance\Concerns;

use Illuminate\Support\Str;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Reports\Report;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatter;

trait RendersReport
{
    /**
     * Render the report in the requested --format and return the exit code:
     * 1 when any check is critical, otherwise 0.
     */
    protected function renderReport(Report $report): int
    {
        $format = (string) $this->option('format');

        switch ($format) {
            case 'json':
                $this->raw($report->toJson(JSON_PRETTY_PRINT));
                break;
            case 'mission-control':
                $this->raw(json_encode($report->toMissionControl(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                break;
            case 'table':
                $this->renderTable($report);
                break;
            default:
                $this->error("Unknown format [{$format}]. Use table, json or mission-control.");

                return self::INVALID;
        }

        return $report->failed() ? self::FAILURE : self::SUCCESS;
    }

    protected function renderTable(Report $report): void
    {
        $this->line('<options=bold>' . $report->fullTitle() . '</>');
        $this->line($report->summary());
        $this->newLine();

        $rows = [];

        foreach ($report->results as $key => $result) {
            $rows[] = [Str::headline($key), $this->statusLabel($result->status), OutputFormatter::escape($result->summary)];
        }

        $this->table(['Check', 'Status', 'Summary'], $rows);
    }

    protected function statusLabel(Status $status): string
    {
        $color = match ($status) {
            Status::Ok => 'green',
            Status::Warning => 'yellow',
            Status::Critical => 'red',
            Status::Unknown => 'gray',
        };

        return "<fg={$color}>{$status->value}</>";
    }

    protected function raw(string $text): void
    {
        $this->output->writeln($text, OutputInterface::OUTPUT_RAW);
    }
}
