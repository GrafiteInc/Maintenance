<?php

use Illuminate\Support\Facades\Artisan;

class PackageCleanerTest extends TestCase
{
    public function testPackageCleaner()
    {
        $this->markTestSkipped('This test is skipped because it requires a specific environment setup.');

        Artisan::call('maintenance:package-cleaner');

        $this->assertStringContainsString('Sorry, try again.', Artisan::output());
    }
}
