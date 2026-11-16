<?php

class PackageCleanerTest extends TestCase
{
    public function testPackageCleanerIsNonInteractiveAndSudoesBothCommands()
    {
        $shell = $this->fakeShell()->fake('sudo *');

        $this->artisan('maintenance:package-cleaner')->assertExitCode(0);

        $this->assertSame([
            "sudo -n apt-get '-y' 'autoremove'",
            "sudo -n apt-get '-y' 'autoclean'",
        ], $shell->commands());
    }
}
