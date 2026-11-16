<?php

namespace Grafite\Maintenance\Testing;

use Illuminate\Support\Str;
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Support\ShellResult;

/**
 * A Shell that never touches the host. Register it with:
 *
 *     $this->app->instance(Shell::class, $shell = new FakeShell());
 */
class FakeShell extends Shell
{
    /** @var array<int, array{command: string, cwd: ?string, stdin: ?string}> */
    public array $executed = [];

    /** @var array<string, ShellResult> */
    protected array $responses = [];

    /** @var array<string, ?string> */
    protected array $files = [];

    protected array $missing = [];

    /**
     * Fake the result of any command matching the pattern (Str::is wildcards).
     */
    public function fake(string $pattern, string $output = '', int $exitCode = 0, string $error = ''): static
    {
        $this->responses[$pattern] = new ShellResult($pattern, $exitCode, $output, $error);

        return $this;
    }

    /**
     * Fake a file's contents. Pass null to mark it missing.
     */
    public function file(string $path, ?string $contents = ''): static
    {
        $this->files[$path] = $contents;

        return $this;
    }

    public function missingBinary(string ...$binaries): static
    {
        $this->missing = array_merge($this->missing, $binaries);

        return $this;
    }

    public function which(string $binary): bool
    {
        return ! in_array($binary, $this->missing, true);
    }

    public function exists(string $path): bool
    {
        return array_key_exists($path, $this->files) && ! is_null($this->files[$path]);
    }

    public function read(string $path): ?string
    {
        return $this->files[$path] ?? null;
    }

    public function ran(string $pattern): bool
    {
        foreach ($this->executed as $execution) {
            if (Str::is($pattern, $execution['command'])) {
                return true;
            }
        }

        return false;
    }

    public function commands(): array
    {
        return array_column($this->executed, 'command');
    }

    protected function execute(string $command, ?string $cwd = null, ?string $stdin = null): ShellResult
    {
        $this->executed[] = compact('command', 'cwd', 'stdin');

        foreach ($this->responses as $pattern => $result) {
            if (Str::is($pattern, $command)) {
                return new ShellResult($command, $result->exitCode, $result->output, $result->error);
            }
        }

        return new ShellResult($command, 127, '', "FakeShell: no fake registered for [{$command}]");
    }
}
