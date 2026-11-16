<?php

namespace Grafite\Maintenance\Checks\Audit;

use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Checks\Status;
use Grafite\Maintenance\Checks\BaseCheck;
use Grafite\Maintenance\Checks\CheckResult;

class AppConfig extends BaseCheck
{
    public function run(Shell $shell): CheckResult
    {
        $environment = (string) app()->environment();
        $production = in_array($environment, (array) config('maintenance.audit.production_environments', ['production']), true);
        $debug = (bool) config('app.debug');
        $url = (string) config('app.url');

        $status = Status::Ok;
        $problems = [];

        $details = [
            'environment' => $environment,
            'debug' => $debug ? 'on' : 'off',
            'app_key' => empty(config('app.key')) ? 'missing' : 'set',
            'app_url' => $url ?: 'not set',
        ];

        if (empty(config('app.key'))) {
            $status = Status::Critical;
            $problems[] = 'APP_KEY is not set';
        }

        if ($production && $debug) {
            $status = Status::Critical;
            $problems[] = "APP_DEBUG is on in {$environment}";
        }

        if ($production && ! str_starts_with($url, 'https://')) {
            $status = $status->worse(Status::Warning);
            $problems[] = "APP_URL is not https in {$environment}";
        }

        return new CheckResult(
            $status,
            empty($problems) ? 'Application config looks safe' : implode('; ', $problems),
            $details
        );
    }
}
