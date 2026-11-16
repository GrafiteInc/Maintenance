<?php

class JournalVacuumTest extends TestCase
{
    public function testJournalVacuum()
    {
        $shell = $this->fakeShell()->fake('sudo *', 'Vacuuming done, freed 1.2G');

        $this->artisan('maintenance:journal-vacuum', ['--time' => '7d'])
            ->expectsOutputToContain('Vacuuming done')
            ->assertExitCode(0);

        $this->assertSame(["sudo -n journalctl '--vacuum-time=7d'"], $shell->commands());
    }
}
