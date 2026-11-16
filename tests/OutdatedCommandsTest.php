<?php

class OutdatedCommandsTest extends TestCase
{
    public function testPhpOutdated()
    {
        $shell = $this->fakeShell()->fake('composer *', $this->fixture('composer-outdated.json'));

        $this->artisan('maintenance:php-outdated')
            ->expectsTable(['Package', 'Current', 'Latest'], [
                ['guzzlehttp/guzzle', '7.8.0', '7.9.2'],
                ['laravel/framework', 'v12.10.0', 'v13.42.0'],
            ])
            ->assertExitCode(0);

        $this->assertSame(base_path(), $shell->executed[0]['cwd']);
    }

    public function testJsOutdatedParsesDespiteNonZeroExit()
    {
        $this->fakeShell()->fake('npm *', $this->fixture('npm-outdated.json'), 1);

        $this->artisan('maintenance:js-outdated')
            ->expectsTable(['Package', 'Current', 'Latest'], [
                ['axios', '1.6.0', '1.7.9'],
                ['vite', 'not installed', '6.0.7'],
            ])
            ->assertExitCode(0);
    }

    public function testNothingOutdated()
    {
        $this->fakeShell()->fake('npm *', '{}');

        $this->artisan('maintenance:js-outdated')
            ->expectsOutput('No packages require updating at this time.')
            ->assertExitCode(0);
    }

    public function testUpgradable()
    {
        $this->fakeShell()->fake('apt *', $this->fixture('apt-upgradable.txt'));

        $this->artisan('maintenance:upgradable')
            ->expectsOutput('Your server has 3 upgradable packages (2 security).')
            ->assertExitCode(0);
    }
}
