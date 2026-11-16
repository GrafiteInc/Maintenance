<?php

use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Checks\Audit\NpmAudit;
use Grafite\Maintenance\Checks\Audit\EnvDrift;
use Grafite\Maintenance\Checks\Audit\AppConfig;
use Grafite\Maintenance\Checks\Audit\ExposedFiles;
use Grafite\Maintenance\Checks\Audit\ComposerAudit;

class AuditChecksTest extends TestCase
{
    public function testComposerAuditFlagsHighSeverityAsCritical()
    {
        $this->temporaryBasePath(['composer.lock' => '{}']);

        // composer audit exits 1 when it finds advisories.
        $shell = $this->fakeShell()->fake('composer *audit*', $this->fixture('composer-audit.json'), 1);

        $result = (new ComposerAudit())->run($shell);

        $this->assertSame(Status::Critical, $result->status);
        $this->assertSame('2 composer advisories (1 high, 1 medium), 1 abandoned package', $result->summary);
        $this->assertSame('league/commonmark: Quadratic complexity in table parsing (CVE-2025-46734, high)', $result->details['advisories'][0]);
        $this->assertSame('symfony/http-foundation: Open redirect via browser-sanitized URLs (PKSA-5678, medium)', $result->details['advisories'][1]);
        $this->assertSame(['fruitcake/laravel-cors'], $result->details['abandoned_packages']);
    }

    public function testComposerAuditClean()
    {
        $this->temporaryBasePath(['composer.lock' => '{}']);

        $shell = $this->fakeShell()->fake('composer *audit*', $this->fixture('composer-audit-clean.json'));

        $this->assertSame(Status::Ok, (new ComposerAudit())->run($shell)->status);
    }

    public function testComposerAuditUnknownWhenUnavailable()
    {
        $this->temporaryBasePath();
        $this->assertSame('No composer.lock to audit', (new ComposerAudit())->run($this->fakeShell())->summary);

        $this->temporaryBasePath(['composer.lock' => '{}']);
        $shell = $this->fakeShell()->fake('composer *audit*', '', 1, 'Command "audit" is not defined.');
        $this->assertSame(Status::Unknown, (new ComposerAudit())->run($shell)->status);
    }

    public function testNpmAudit()
    {
        $this->temporaryBasePath(['package-lock.json' => '{}']);

        $shell = $this->fakeShell()->fake('npm *audit*', $this->fixture('npm-audit.json'), 1);
        $result = (new NpmAudit())->run($shell);

        $this->assertSame(Status::Critical, $result->status);
        $this->assertSame('2 npm vulnerabilities (1 high, 1 moderate)', $result->summary);
        $this->assertSame(['cross-spawn (high)', 'nanoid (moderate)'], $result->details['packages']);

        $shell = $this->fakeShell()->fake('npm *audit*', $this->fixture('npm-audit-clean.json'));
        $this->assertSame(Status::Ok, (new NpmAudit())->run($shell)->status);
    }

    public function testAppConfig()
    {
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);

        $this->assertSame(Status::Ok, (new AppConfig())->run($this->fakeShell())->status);

        $this->app['env'] = 'production';
        config(['app.debug' => true, 'app.url' => 'http://example.test']);

        $result = (new AppConfig())->run($this->fakeShell());
        $this->assertSame(Status::Critical, $result->status);
        $this->assertSame('APP_DEBUG is on in production; APP_URL is not https in production', $result->summary);

        config(['app.debug' => false, 'app.key' => null, 'app.url' => 'https://example.test']);
        $this->assertSame('APP_KEY is not set', (new AppConfig())->run($this->fakeShell())->summary);
    }

    public function testEnvDriftReportsKeyNamesOnly()
    {
        $this->temporaryBasePath([
            '.env.example' => "APP_NAME=\nAPP_KEY=\n# COMMENTED=1\nexport MAIL_HOST=\nSTRIPE_SECRET=\n",
            '.env' => "APP_NAME=Real\nAPP_KEY=base64:supersecret\n",
        ]);

        $result = (new EnvDrift())->run($this->fakeShell());

        $this->assertSame(Status::Warning, $result->status);
        $this->assertSame(['MAIL_HOST', 'STRIPE_SECRET'], $result->details['missing_keys']);
        $this->assertStringNotContainsString('supersecret', json_encode($result->toArray()));
    }

    public function testEnvDriftUnknownWithoutFiles()
    {
        $this->temporaryBasePath(['.env' => 'APP_KEY=x']);

        $this->assertSame(Status::Unknown, (new EnvDrift())->run($this->fakeShell())->status);
    }

    public function testExposedFiles()
    {
        $base = $this->temporaryBasePath(['.env' => 'APP_KEY=x']);
        chmod($base . '/.env', 0600);

        $this->assertSame(Status::Ok, (new ExposedFiles())->run($this->fakeShell())->status);

        chmod($base . '/.env', 0644);
        $this->assertSame(Status::Warning, (new ExposedFiles())->run($this->fakeShell())->status);

        mkdir($base . '/public/.git');
        file_put_contents($base . '/public/phpinfo.php', '<?php phpinfo();');

        $result = (new ExposedFiles())->run($this->fakeShell());
        $this->assertSame(Status::Critical, $result->status);
        $this->assertSame(['public/.git', 'public/phpinfo.php'], $result->details['public_files']);
    }
}
