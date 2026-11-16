<?php

use Grafite\Maintenance\Checks\Check;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Reports\Report;
use Grafite\Maintenance\Checks\CheckResult;
use Grafite\Maintenance\Reports\HealthReport;
use Grafite\Maintenance\Reports\SecurityAudit;

class ReportTest extends TestCase
{
    public function testStatusIsWorstKnownStatus()
    {
        $this->assertSame(Status::Warning, $this->report([
            'a' => CheckResult::ok('fine'),
            'b' => CheckResult::warning('hmm'),
            'c' => CheckResult::unknown('not on this host'),
        ])->status());

        $this->assertSame(Status::Critical, $this->report([
            'a' => CheckResult::warning('hmm'),
            'b' => CheckResult::critical('bad'),
        ])->status());

        $this->assertSame(Status::Ok, $this->report([
            'a' => CheckResult::ok('fine'),
            'b' => CheckResult::unknown('not on this host'),
        ])->status());

        $this->assertSame(Status::Unknown, $this->report([
            'a' => CheckResult::unknown('not on this host'),
        ])->status());
    }

    public function testSummaryCounts()
    {
        $report = $this->report([
            'a' => CheckResult::ok('fine'),
            'b' => CheckResult::warning('hmm'),
            'c' => CheckResult::unknown('?'),
        ]);

        $this->assertSame('0 critical, 1 warning, 1 ok, 1 unknown', $report->summary());
    }

    public function testMissionControlPayloadShape()
    {
        $payload = $this->report([
            'php_version' => CheckResult::critical('PHP 8.1 reached end of life on 2025-12-31', ['version' => '8.1.33']),
        ])->toMissionControl();

        $this->assertSame(['title', 'tag', 'message'], array_keys($payload));
        $this->assertSame('Health Report: CRITICAL (web-1)', $payload['title']);
        $this->assertSame('maintenance:health:critical', $payload['tag']);

        $message = json_decode($payload['message'], true);
        $this->assertSame('critical', $message['status']);
        $this->assertSame('web-1', $message['host']);
        $this->assertSame('PHP 8.1 reached end of life on 2025-12-31', $message['checks']['php_version']['summary']);
    }

    public function testPayloadSpreadsIntoMissionControlNotifySignature()
    {
        // Same signature as grafite/mission-control-laravel's mission_control_notify().
        $notify = fn ($title, $tag = null, $message = null) => compact('title', 'tag', 'message');

        $payload = $this->report(['a' => CheckResult::ok('fine')])->toMissionControl();

        $this->assertSame($payload, $notify(...$payload));
    }

    public function testTitleIsCappedAt255Characters()
    {
        $report = new Report('Health Report', 'maintenance:health', [], str_repeat('h', 300), 'production', '2026-11-12 07:00:00');

        $this->assertSame(255, mb_strlen($report->toMissionControl()['title']));
    }

    public function testTagWithoutStatus()
    {
        $report = new Report('Health Report', 'maintenance:health', [], 'web-1', 'production', 'now', appendStatusToTag: false);

        $this->assertSame('maintenance:health', $report->fullTag());
    }

    public function testMessageHasNoValuesMissionControlWouldRenderAsNa()
    {
        $report = $this->report([
            'counts' => CheckResult::ok('Zero is data', [
                'failed' => 0,
                'string_zero' => '0',
                'empty' => '',
                'list' => [],
                'nested' => ['size' => 0.0, 'kept' => 'value'],
            ]),
        ]);

        $leaves = [];
        $message = json_decode($report->toMissionControl()['message'], true);
        array_walk_recursive($message, function ($value) use (&$leaves) {
            $leaves[] = $value;
        });

        foreach ($leaves as $value) {
            // Mission Control's jsonToTable() renders any empty() leaf as "N/A".
            $this->assertFalse(empty($value), 'Found a value Mission Control renders as N/A: ' . var_export($value, true));
            $this->assertStringNotContainsString('<', (string) $value);
        }
    }

    public function testRealChecksProduceMissionControlSafePayloads()
    {
        $this->travelTo('2026-11-12 07:00:00');
        config([
            'maintenance.host' => 'web-1',
            // Keep the running PHP version supported so the result doesn't depend on it.
            'maintenance.report.php_eol' => [PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION => '2099-12-31'],
        ]);

        $this->fakeShell()
            ->fake("apt 'list' '--upgradable'", $this->fixture('apt-upgradable.txt'))
            ->file('/var/run/reboot-required', '1');

        $payload = app(HealthReport::class)->run()->toMissionControl();
        $message = json_decode($payload['message'], true);

        $this->assertSame('Health Report: WARNING (web-1)', $payload['title']);
        $this->assertSame('maintenance:health:warning', $payload['tag']);
        $this->assertSame('2026-11-12 07:00:00', $message['generated_at']);
        $this->assertSame(
            ['reboot_required', 'server_packages', 'php_version', 'outdated_packages'],
            array_keys($message['checks'])
        );

        array_walk_recursive($message, function ($value, $key) {
            $this->assertFalse(empty($value), "[{$key}] would render as N/A in Mission Control");
        });
    }

    public function testRunnerTurnsExceptionsIntoUnknown()
    {
        config(['maintenance.audit.checks' => [new class implements Check {
            public function key(): string
            {
                return 'explodes';
            }

            public function run(Shell $shell): CheckResult
            {
                throw new RuntimeException('Boom');
            }
        }]]);

        $report = app(SecurityAudit::class)->run();

        $this->assertSame('security:audit:unknown', $report->fullTag());
        $this->assertSame('Check failed: Boom', $report->results['explodes']->summary);
    }

    protected function report(array $results): Report
    {
        return new Report('Health Report', 'maintenance:health', $results, 'web-1', 'production', '2026-11-12 07:00:00');
    }
}
