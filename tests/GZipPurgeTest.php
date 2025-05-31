<?php

use Illuminate\Support\Facades\Artisan;

class GZipPurgeTest extends TestCase
{
    public function testGZipPurge()
    {
        Artisan::call('maintenance:gzip-purge');

        $this->assertStringContainsString('No such file or directory', Artisan::output());
    }
}
