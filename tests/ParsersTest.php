<?php

use Grafite\Maintenance\Parsers\NpmOutdated;
use Grafite\Maintenance\Parsers\AptUpgradable;
use Grafite\Maintenance\Parsers\ComposerOutdated;

class ParsersTest extends TestCase
{
    public function testAptUpgradable()
    {
        $upgradable = AptUpgradable::parse($this->fixture('apt-upgradable.txt'));

        $this->assertSame(3, $upgradable['total']);
        $this->assertSame(2, $upgradable['security']);
        $this->assertSame([
            'name' => 'libssl3',
            'from' => '3.0.2-0ubuntu1.14',
            'to' => '3.0.2-0ubuntu1.15',
            'security' => true,
        ], $upgradable['packages'][0]);

        $this->assertSame(0, AptUpgradable::parse($this->fixture('apt-upgradable-none.txt'))['total']);
    }

    public function testComposerOutdated()
    {
        $packages = ComposerOutdated::parse($this->fixture('composer-outdated.json'));

        $this->assertCount(2, $packages);
        $this->assertSame('laravel/framework', $packages[1]['name']);
        $this->assertSame('v13.42.0', $packages[1]['latest']);
        $this->assertSame([], ComposerOutdated::parse('{"installed": []}'));
        $this->assertNull(ComposerOutdated::parse('Composer could not find a composer.json'));
    }

    public function testNpmOutdated()
    {
        $packages = NpmOutdated::parse($this->fixture('npm-outdated.json'));

        $this->assertSame(['name' => 'axios', 'current' => '1.6.0', 'latest' => '1.7.9'], $packages[0]);
        $this->assertSame('not installed', $packages[1]['current']);
        $this->assertSame([], NpmOutdated::parse('{}'));
        $this->assertSame([], NpmOutdated::parse(''));
        $this->assertNull(NpmOutdated::parse('npm ERR! missing script'));
    }
}
