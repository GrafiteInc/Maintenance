<?php

use Illuminate\Support\Facades\Artisan;

class PackageCleanerTest extends TestCase
{
    public function testPackageCleaner()
    {
        Artisan::call('maintenance:package-cleaner');

        $this->assertStringContainsString('Sorry, try again.', Artisan::output());
    }
}
