<?php

use Grafite\Maintenance\Checks\Check;
use Grafite\Maintenance\Support\Shell;
use Illuminate\Support\Facades\Artisan;
use Grafite\Maintenance\Checks\CheckResult;

class ReportCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config(['maintenance.host' => 'web-1']);
    }

    public function testTableFormat()
    {
        $this->useChecks('report', CheckResult::warning('/ at 84% used'));

        $this->artisan('maintenance:report')
            ->expectsOutputToContain('Health Report: WARNING (web-1)')
            ->expectsOutputToContain('/ at 84% used')
            ->assertExitCode(0);
    }

    public function testJsonFormat()
    {
        $this->useChecks('report', CheckResult::ok('fine'));

        Artisan::call('maintenance:report', ['--format' => 'json']);
        $data = json_decode(Artisan::output(), true);

        $this->assertSame('ok', $data['status']);
        $this->assertSame('fine', $data['checks']['stub']['summary']);
    }

    public function testMissionControlFormat()
    {
        $this->useChecks('audit', CheckResult::critical('APP_DEBUG is on in production'));

        $exitCode = Artisan::call('maintenance:audit', ['--format' => 'mission-control']);
        $payload = json_decode(Artisan::output(), true);

        $this->assertSame(1, $exitCode, 'critical exits 1');
        $this->assertSame('Security Audit: CRITICAL (web-1)', $payload['title']);
        $this->assertSame('security:audit:critical', $payload['tag']);
        $this->assertSame('APP_DEBUG is on in production', json_decode($payload['message'], true)['checks']['stub']['summary']);
    }

    public function testInvalidFormat()
    {
        $this->useChecks('report', CheckResult::ok('fine'));

        $this->artisan('maintenance:report', ['--format' => 'xml'])->assertExitCode(2);
    }

    protected function useChecks(string $section, CheckResult $result): void
    {
        config(["maintenance.{$section}.checks" => [new class($result) implements Check {
            public function __construct(private CheckResult $result)
            {
            }

            public function key(): string
            {
                return 'stub';
            }

            public function run(Shell $shell): CheckResult
            {
                return $this->result;
            }
        }]]);
    }
}
