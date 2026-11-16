![Grafite Maintenance](GrafiteMaintenance-banner.png)

**Maintenance** - A set of handy commands for app maintanence.

[![Build Status](https://github.com/GrafiteInc/Maintenance/actions/workflows/php-package-tests.yml/badge.svg?branch=main)](https://github.com/GrafiteInc/Maintenance/actions/workflows/php-package-tests.yml)
[![Maintainability](https://qlty.sh/badges/3d7cf51b-5cc4-484f-afba-53a0e79a0644/maintainability.svg)](https://qlty.sh/gh/GrafiteInc/projects/Maintenance)
[![Packagist](https://img.shields.io/packagist/dt/grafite/maintenance.svg)](https://packagist.org/packages/grafite/maintenance)
[![license](https://img.shields.io/github/license/mashape/apistatus.svg)](https://packagist.org/packages/grafite/maintenance)

The Grafite Maintenance package is a collection of commands to help with app maintenance. This package has commands which can help delete old logs, and system leftovers which can eat up server space over time.

##### Author(s):
* [Matt Lantz](https://github.com/mlantz) ([@mattylantz](http://twitter.com/mattylantz), mattlantz at gmail dot com)

## Requirements

1. PHP 7.4+|8.2+

## Compatibility and Support

| Laravel Version | Package Tag | Supported |
|-----------------|-------------|-----------|
| ^7.x - ^12.x | 0.6.x | yes |

### Installation

Start a new Laravel project:
```php
composer create-project laravel/laravel your-project-name
```

Then run the following to add Grafite Maintenance
```php
composer require "grafite/maintenance"
```

## Documentation

[https://documentation.grafite.ca/docs/utilities-maintenance](https://documentation.grafite.ca/docs/utilities-maintenance)

### Configuration

```
php artisan vendor:publish --tag=maintenance-config
```

### Health Report & Security Audit

```
php artisan maintenance:report                      # reboot required, apt security updates, PHP EOL, outdated packages
php artisan maintenance:audit                       # composer/npm audit, APP_DEBUG/APP_KEY/https, .env drift, exposed public files
php artisan maintenance:report --format=json
php artisan maintenance:report --format=mission-control
```

Both commands exit with `1` when any check is critical. Checks that can't run on the host (for example apt checks on macOS) report `unknown` and don't affect the overall status.

The package only builds the payload. It never sends it anywhere, so you decide where it goes. The `mission-control` format, and `toMissionControl()` in code, return `title`, `tag` and `message` in the shape [Mission Control's](https://github.com/GrafiteInc/Mission-Control-Laravel-Package) notify API expects:

```php
use Grafite\Maintenance\Reports\HealthReport;
use Grafite\Maintenance\Reports\SecurityAudit;
use Illuminate\Support\Facades\Schedule;

Schedule::call(fn () => mission_control_notify(...app(HealthReport::class)->run()->toMissionControl()))->dailyAt('07:00');
Schedule::call(fn () => mission_control_notify(...app(SecurityAudit::class)->run()->toMissionControl()))->weeklyOn(1, '07:00');
```

The `message` is a JSON string, which Mission Control renders as a table. By default the overall status is appended to the tag (`maintenance:health:critical`, `security:audit:warning`), so you can add a Mission Control notification listener for the exact severity you want alerts on.

For a different destination, use `$report->toArray()`, `$report->fullTitle()`, `$report->status()` and the other methods on `Grafite\Maintenance\Reports\Report`. From the shell, the exit code is non-zero on critical, so capture the output first:

```
payload=$(php artisan maintenance:report --format=mission-control) || true
```

Add your own checks by implementing `Grafite\Maintenance\Checks\Check` and listing the class in `maintenance.report.checks` or `maintenance.audit.checks`.

### Sudo

`maintenance:log-purge`, `maintenance:journal-vacuum` and `maintenance:package-cleaner` need root. The recommended setup is a sudoers rule for just these commands (`sudo visudo -f /etc/sudoers.d/maintenance`):

```
forge ALL=(root) NOPASSWD: /usr/bin/find /var/log *, /usr/bin/journalctl --vacuum-time=*, /usr/bin/apt-get -y autoremove, /usr/bin/apt-get -y autoclean
```

Set `MAINTENANCE_SUDO=password` to keep using `SUDO_PW`. The password is sent to `sudo -S` over stdin. Use `MAINTENANCE_SUDO=none` when the commands already run as root.

### Testing your own code

Swap the shell for a fake so that nothing runs on the host:

```php
use Grafite\Maintenance\Support\Shell;
use Grafite\Maintenance\Testing\FakeShell;

$this->app->instance(Shell::class, $shell = (new FakeShell())->fake('df *', $dfOutput));
```


## License
Grafite Database is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Bug Reporting and Feature Requests
Please add as many details as possible regarding submission of issues and feature requests

### Disclaimer
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
