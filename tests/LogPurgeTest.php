<?php

use Illuminate\Support\Facades\Artisan;

class LogPurgeTest extends TestCase
{
    public function testLogPurge()
    {
        Artisan::call('maintenance:log-purge');

        $this->assertStringContainsString('Sorry, try again.', Artisan::output());
    }
}
