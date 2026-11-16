<?php

namespace Grafite\Maintenance\Support;

final class ShellResult
{
    public function __construct(
        public readonly string $command,
        public readonly int $exitCode,
        public readonly string $output = '',
        public readonly string $error = '',
    ) {
    }

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    /**
     * Decode stdout as JSON regardless of the exit code. Tools like `npm outdated`,
     * `npm audit` and `composer audit` exit non-zero when they find something.
     */
    public function json(): ?array
    {
        $decoded = json_decode($this->output, true);

        return is_array($decoded) ? $decoded : null;
    }
}
