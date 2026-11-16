<?php

use Grafite\Maintenance\Support\Shell;

class ShellTest extends TestCase
{
    public function testArgumentsAreEscaped()
    {
        $command = (new Shell())->command('find', ['/tmp/it\'s here', '-name', '*.gz', '; rm -rf /']);

        $this->assertSame("find '/tmp/it'\\''s here' '-name' '*.gz' '; rm -rf /'", $command);
    }

    public function testBinariesResolveFromConfigUnescaped()
    {
        config(['maintenance.binaries.composer' => 'php /opt/composer.phar']);

        $this->assertSame("php /opt/composer.phar 'audit'", (new Shell())->command('composer', ['audit']));
    }

    public function testSudoStrategies()
    {
        $shell = new Shell();

        config(['maintenance.sudo.strategy' => 'sudoers']);
        $this->assertSame("sudo -n apt-get '-y' 'autoclean'", $shell->command('apt-get', ['-y', 'autoclean'], true));

        config(['maintenance.sudo.strategy' => 'password']);
        $this->assertSame("sudo -S -k -p '' apt-get '-y' 'autoclean'", $shell->command('apt-get', ['-y', 'autoclean'], true));

        config(['maintenance.sudo.strategy' => 'none']);
        $this->assertSame("apt-get '-y' 'autoclean'", $shell->command('apt-get', ['-y', 'autoclean'], true));
    }

    public function testPasswordIsSentOnStdinNotTheCommandLine()
    {
        config(['maintenance.sudo.strategy' => 'password', 'maintenance.sudo.password' => "s3cret'pw"]);

        $shell = $this->fakeShell()->fake('*journalctl*');
        $shell->run('journalctl', ['--vacuum-time=2d'], sudo: true);

        $this->assertSame("s3cret'pw\n", $shell->executed[0]['stdin']);
        $this->assertStringNotContainsString('s3cret', $shell->executed[0]['command']);
    }

    public function testSudoStrategyDefaultsToSudoersWithoutSudoPw()
    {
        $config = require __DIR__ . '/../config/maintenance.php';

        $this->assertSame('sudoers', $config['sudo']['strategy']);
    }

    public function testRealShellRunsCommands()
    {
        $result = (new Shell())->run('echo', ['hello world']);

        $this->assertTrue($result->successful());
        $this->assertSame('hello world', $result->output);
        $this->assertTrue((new Shell())->which('echo'));
        $this->assertFalse((new Shell())->which('definitely-not-a-real-binary-xyz'));
    }
}
