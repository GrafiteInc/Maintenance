<?php

use Illuminate\Support\Facades\Artisan;

class JournalVacuumTest extends TestCase
{
    public function testJournalVacuum()
    {
        $this->markTestSkipped('This test is skipped because it requires a specific environment setup.');

        Artisan::call('maintenance:journal-vacuum');

        $this->assertStringContainsString('Sorry, try again.', Artisan::output());
    }
}
