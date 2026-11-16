<?php

namespace Grafite\Maintenance\Checks;

use Illuminate\Support\Str;

abstract class BaseCheck implements Check
{
    public function key(): string
    {
        return Str::snake(class_basename(static::class));
    }

    protected function threshold(string $name): array
    {
        return (array) config("maintenance.report.thresholds.{$name}", []);
    }
}
