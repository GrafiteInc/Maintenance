<?php

use Illuminate\Support\Facades\Artisan;

class LogPurgeTest extends TestCase
{
    public function testLogPurge()
    {
        $this->markTestSkipped('This test is skipped because it requires a specific environment setup.');

        Artisan::call('maintenance:log-purge');

        $this->assertStringContainsString('Sorry, try again.', Artisan::output());
    }
}
