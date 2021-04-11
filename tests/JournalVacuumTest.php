<?php

use Illuminate\Support\Facades\Artisan;

class JournalVacuumTest extends TestCase
{
    public function testJournalVacuum()
    {
        Artisan::call('maintenance:journal-vacuum');
    }
}
