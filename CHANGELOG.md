# Change Log - Grafite Maintenance
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).
----

## [v0.8.0] - Unreleased

### Feature
- `maintenance:report` health snapshot: reboot required, apt security updates, PHP end of life, outdated packages
- `maintenance:audit` security audit: composer audit, npm audit, APP_DEBUG/APP_KEY/https checks, .env drift, exposed public files
- `--format=table|json|mission-control`; `Report::toMissionControl()` returns arguments ready for `mission_control_notify()`
- Publishable `config/maintenance.php` (sudo strategy, binaries, thresholds, checks, tags)
- `FakeShell` for testing

### Fix
- `maintenance:gzip-purge --path=` now takes a value (it was a boolean flag)
- Shell arguments are escaped
- Every sudo call gets the password; previously the second command in `log-purge` and `package-cleaner` ran without it
- `package-cleaner` runs `apt-get -y` so it no longer hangs waiting for confirmation
- `SUDO_PW` is read through config, so it works with `config:cache`
- `js-outdated` no longer treats npm's exit code 1 (outdated packages found) as an error
- `maintenance:updating` serves the page with a 503 + `Retry-After`, and refuses to run twice, which used to overwrite the backup
- `maintenance:live` and `maintenance:log-archive` fail gracefully when there is no backup or disk
- Removed the hardcoded `/usr/local/bin/composer` and vendor paths

### Breaking
- Sudo now defaults to `sudo -n` (sudoers) unless `SUDO_PW` is set or `MAINTENANCE_SUDO` says otherwise
- `php-outdated`, `js-outdated` print console tables instead of HTML
- Commands return proper exit codes

## [v0.7.0] - 2026-04-07

### Feature
- version upgrades

### Chore
- general updates

## [v0.6.3] - 2025-05-31

### Chore
- update phpunit
- update readme

### Fix
- test corrections

## [v0.6.2] - 2025-05-05

### Chore
-  dependency updates

## [v0.6.1] - 2024-04-25

### Fixed
- Issue with file loading

## [v0.6.0] - 2024-04-25

### Added
- Attempt at simple maintenance mode

## [v0.5.2] - 2024-04-11

### Fixed
- Missing command

## [v0.5.1] - 2024-04-11

### Fixed
- Missing command

## [v0.5.0] - 2024-03-29

### Added
- New log archiving tool

## [v0.4.0] - 2024-03-12

### Added
- Support for Laravel 11

## [v0.3.1] - 2023-03-07

### Fixed
- Small issue with composer

## [v0.3.0] - 2023-03-03

### Added
- Laravel 10 compatibility

## [v0.2.4] - 2022-02-08

### Added
- Laravel 9 compatibility

## [v0.2.3] - 2021-05-05

### Fixed
- Issue with path for gzip cleanup

## [v0.2.2] - 2021-04-26

### Fixed
- Issue with dependencies

## [v0.2.1] - 2021-04-17

### Added
- New Upgradeable packages command

### Fixed
- Issues with parsers

## [v0.2.0] - 2021-04-15

### Added
- Outdated dependency scanning

## [v0.1.0] - 2021-04-11

### Added
- Initial build and release
