<?php

use Illuminate\Support\Facades\Artisan;

class GZipPurgeTest extends TestCase
{
    public function testGZipPurge()
    {
        Artisan::call('maintenance:gzip-purge');
    }
}
