<?php

class GZipPurgeTest extends TestCase
{
    public function testGZipPurgeDefaultsToStorageLogs()
    {
        $shell = $this->fakeShell()->fake('find *', "/logs/a.gz\n/logs/b.gz");

        $this->artisan('maintenance:gzip-purge')
            ->expectsOutputToContain('Deleted 2 gzip files')
            ->assertExitCode(0);

        $this->assertSame(
            'find ' . escapeshellarg(storage_path('logs')) . " '-maxdepth' '1' '-type' 'f' '-name' '*.gz' '-print' '-delete'",
            $shell->commands()[0]
        );
    }

    public function testGZipPurgeAcceptsAPath()
    {
        $shell = $this->fakeShell()->fake('find *');
        $path = sys_get_temp_dir();

        $this->artisan('maintenance:gzip-purge', ['--path' => $path])->assertExitCode(0);

        $this->assertStringStartsWith('find ' . escapeshellarg($path) . ' ', $shell->commands()[0]);
    }

    public function testGZipPurgeRejectsMissingDirectories()
    {
        $shell = $this->fakeShell();

        $this->artisan('maintenance:gzip-purge', ['--path' => '/nope/does-not-exist'])->assertExitCode(1);

        $this->assertEmpty($shell->executed);
    }
}
