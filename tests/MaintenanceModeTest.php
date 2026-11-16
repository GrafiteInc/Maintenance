<?php

class MaintenanceModeTest extends TestCase
{
    public function testUpdatingThenLive()
    {
        $base = $this->temporaryBasePath(['public/index.php' => '<?php echo "app";']);

        $this->artisan('maintenance:updating', ['--retry' => 600])->assertExitCode(0);

        $stub = file_get_contents($base . '/public/index.php');
        $this->assertStringContainsString('http_response_code(503);', $stub);
        $this->assertStringContainsString("header('Retry-After: 600');", $stub);
        $this->assertStringContainsString('Site Maintenance', $stub);
        $this->assertSame('<?php echo "app";', file_get_contents($base . '/public/index-bk.php'));

        // Running it twice must not overwrite the backup with the stub.
        $this->artisan('maintenance:updating')->assertExitCode(1);
        $this->assertSame('<?php echo "app";', file_get_contents($base . '/public/index-bk.php'));

        $this->artisan('maintenance:live')->assertExitCode(0);
        $this->assertSame('<?php echo "app";', file_get_contents($base . '/public/index.php'));
        $this->assertFileDoesNotExist($base . '/public/index-bk.php');
    }

    public function testStubIsValidPhp()
    {
        $base = $this->temporaryBasePath(['public/index.php' => '<?php']);

        $this->artisan('maintenance:updating');

        exec('php -l ' . escapeshellarg($base . '/public/index.php'), $output, $exitCode);
        $this->assertSame(0, $exitCode, implode("\n", $output));
    }

    public function testLiveWithoutBackup()
    {
        $this->temporaryBasePath(['public/index.php' => '<?php']);

        $this->artisan('maintenance:live')->assertExitCode(1);
    }
}
