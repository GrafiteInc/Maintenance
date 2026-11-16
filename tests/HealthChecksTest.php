<?php

use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Checks\Health\PhpVersion;
use Grafite\Maintenance\Checks\Health\RebootRequired;
use Grafite\Maintenance\Checks\Health\ServerPackages;
use Grafite\Maintenance\Checks\Health\OutdatedPackages;

class HealthChecksTest extends TestCase
{
    public function testRebootRequired()
    {
        $shell = $this->fakeShell();
        $this->assertSame(Status::Ok, (new RebootRequired())->run($shell)->status);

        $shell->file('/var/run/reboot-required', '*** System restart required ***')
            ->file('/var/run/reboot-required.pkgs', "linux-base\nlinux-base\nlibc6\n");

        $result = (new RebootRequired())->run($shell);
        $this->assertSame(Status::Warning, $result->status);
        $this->assertSame(['linux-base', 'libc6'], $result->details['packages']);

        $this->assertSame(Status::Unknown, (new RebootRequired())->run($this->fakeShell()->missingBinary('apt-get'))->status);
    }

    public function testServerPackagesWarnsOnSecurityUpdates()
    {
        $shell = $this->fakeShell()->fake("apt 'list' '--upgradable'", $this->fixture('apt-upgradable.txt'));

        $result = (new ServerPackages())->run($shell);

        $this->assertSame(Status::Warning, $result->status);
        $this->assertSame('2 security updates pending (3 upgradable)', $result->summary);
        $this->assertSame('openssl 3.0.2-0ubuntu1.14 → 3.0.2-0ubuntu1.15', $result->details['security_updates'][1]);
    }

    public function testServerPackagesOk()
    {
        $shell = $this->fakeShell()->fake("apt 'list' '--upgradable'", $this->fixture('apt-upgradable-none.txt'));

        $this->assertSame(Status::Ok, (new ServerPackages())->run($shell)->status);
    }

    public function testPhpVersionAgainstEndOfLife()
    {
        $this->travelTo('2026-11-12');

        $this->assertSame(Status::Ok, $this->phpVersion('8.4.10')->status);

        $warning = $this->phpVersion('8.2.29');
        $this->assertSame(Status::Warning, $warning->status);
        $this->assertSame('PHP 8.2 security support ends in 49 days', $warning->summary);

        $this->assertSame(Status::Critical, $this->phpVersion('8.1.33')->status);
        $this->assertSame(Status::Ok, $this->phpVersion('9.0.0')->status);

        config(['maintenance.report.php_eol' => ['8.4' => '2026-12-01']]);
        $this->assertSame(Status::Warning, $this->phpVersion('8.4.10')->status);
    }

    public function testOutdatedPackagesIsInformational()
    {
        $base = $this->temporaryBasePath(['composer.json' => '{}', 'package.json' => '{}']);

        $shell = $this->fakeShell()
            ->fake('composer *outdated*', $this->fixture('composer-outdated.json'))
            ->fake('npm *outdated*', $this->fixture('npm-outdated.json'), 1);

        $result = (new OutdatedPackages())->run($shell);

        $this->assertSame(Status::Ok, $result->status);
        $this->assertSame('2 composer packages, 2 npm packages outdated', $result->summary);
        $this->assertSame('laravel/framework v12.10.0 → v13.42.0', $result->details['composer'][1]);
        $this->assertSame($base, $shell->executed[0]['cwd']);
    }

    protected function phpVersion(string $version)
    {
        $check = new class($version) extends PhpVersion {
            public function __construct(private string $fakeVersion)
            {
            }

            protected function version(): string
            {
                return $this->fakeVersion;
            }
        };

        return $check->run($this->fakeShell());
    }
}
