<?php

namespace Grafite\Maintenance\Support;

use mikehaertl\shellcommand\Command;

/**
 * The single gateway to the host system: shell commands, file reads and
 * load averages. Swap it for Grafite\Maintenance\Testing\FakeShell in tests.
 */
class Shell
{
    /**
     * Run a binary with escaped arguments.
     *
     * The binary is resolved through `maintenance.binaries` and is not escaped
     * (it is trusted config). Every argument is escaped.
     */
    public function run(string $binary, array $args = [], bool $sudo = false, ?string $cwd = null): ShellResult
    {
        $command = $this->command($binary, $args, $sudo);

        $stdin = null;

        if ($sudo && $this->sudoStrategy() === 'password') {
            $stdin = (string) config('maintenance.sudo.password') . "\n";
        }

        return $this->execute($command, $cwd, $stdin);
    }

    /**
     * Build the command line that run() executes.
     */
    public function command(string $binary, array $args = [], bool $sudo = false): string
    {
        $line = trim($this->binary($binary) . ' ' . implode(' ', array_map('escapeshellarg', $args)));

        if (! $sudo) {
            return $line;
        }

        return match ($this->sudoStrategy()) {
            'none' => $line,
            'password' => "sudo -S -k -p '' {$line}",
            default => "sudo -n {$line}",
        };
    }

    public function binary(string $name): string
    {
        return (string) config("maintenance.binaries.{$name}", $name);
    }

    /**
     * Whether a binary is available on the PATH.
     */
    public function which(string $binary): bool
    {
        $executable = strtok($this->binary($binary), ' ');

        return $this->execute('command -v ' . escapeshellarg($executable))->successful();
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function read(string $path): ?string
    {
        if (! is_readable($path) || is_dir($path)) {
            return null;
        }

        $contents = @file_get_contents($path);

        return $contents === false ? null : $contents;
    }

    protected function sudoStrategy(): string
    {
        return (string) config('maintenance.sudo.strategy', 'sudoers');
    }

    protected function execute(string $command, ?string $cwd = null, ?string $stdin = null): ShellResult
    {
        $shell = new Command([
            'command' => $command,
            'procCwd' => $cwd,
            'timeout' => (int) config('maintenance.timeout', 300),
        ]);

        if (! is_null($stdin)) {
            $shell->setStdIn($stdin);
        }

        $shell->execute();

        return new ShellResult(
            $command,
            (int) $shell->getExitCode(),
            $shell->getOutput(),
            $shell->getError(),
        );
    }
}
