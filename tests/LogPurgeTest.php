<?php

class LogPurgeTest extends TestCase
{
    public function testLogPurgeRunsEachFindWithSudo()
    {
        config(['maintenance.sudo.strategy' => 'password', 'maintenance.sudo.password' => 'pw']);

        $shell = $this->fakeShell()->fake('sudo *');

        $this->artisan('maintenance:log-purge')->assertExitCode(0);

        $this->assertSame([
            "sudo -S -k -p '' find '/var/log' '-type' 'f' '-regex' '.*\\.gz$' '-delete'",
            "sudo -S -k -p '' find '/var/log' '-type' 'f' '-regex' '.*\\.[0-9]$' '-delete'",
        ], $shell->commands());

        // Each sudo call gets the password, not just the first.
        $this->assertSame(["pw\n", "pw\n"], array_column($shell->executed, 'stdin'));
    }

    public function testLogPurgeReportsFailures()
    {
        $this->fakeShell()->fake('sudo *', '', 1, 'sudo: a password is required');

        $this->artisan('maintenance:log-purge')
            ->expectsOutputToContain('sudo: a password is required')
            ->assertExitCode(1);
    }
}
