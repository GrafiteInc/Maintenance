<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sudo
    |--------------------------------------------------------------------------
    |
    | How commands that need root (log purge, journal vacuum, package cleaner)
    | are elevated:
    |
    | - "sudoers": runs `sudo -n`, which needs a NOPASSWD sudoers rule for the
    |   specific commands (recommended, see the README).
    | - "password": pipes the password to `sudo -S` over stdin.
    | - "none": runs the commands as-is (already root, or in a container).
    |
    */

    'sudo' => [
        'strategy' => env('MAINTENANCE_SUDO', env('SUDO_PW') ? 'password' : 'sudoers'),
        'password' => env('SUDO_PW'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Binaries
    |--------------------------------------------------------------------------
    |
    | Paths to the binaries the package calls. These are trusted values and are
    | not escaped, so "php /path/to/composer.phar" works too.
    |
    */

    'binaries' => [
        'composer' => env('MAINTENANCE_COMPOSER', 'composer'),
        'npm' => env('MAINTENANCE_NPM', 'npm'),
        'apt' => 'apt',
        'apt-get' => 'apt-get',
        'journalctl' => 'journalctl',
    ],

    // Maximum seconds any single shell command may run.
    'timeout' => 300,

    // The host name used in report titles and payloads. Defaults to gethostname().
    'host' => env('MAINTENANCE_HOST'),

    // Append the overall status to the report tag (e.g. "maintenance:health:critical")
    // so Mission Control listeners can target a specific severity.
    'append_status_to_tag' => true,

    /*
    |--------------------------------------------------------------------------
    | Health Report (maintenance:report)
    |--------------------------------------------------------------------------
    */

    'report' => [
        'title' => 'Health Report',
        'tag' => 'maintenance:health',

        'checks' => [
            Grafite\Maintenance\Checks\Health\RebootRequired::class,
            Grafite\Maintenance\Checks\Health\ServerPackages::class,
            Grafite\Maintenance\Checks\Health\PhpVersion::class,
            Grafite\Maintenance\Checks\Health\OutdatedPackages::class,
        ],

        'thresholds' => [
            // Days before PHP's end of security support to start warning.
            'php_eol_days' => 180,
        ],

        // End of security support per PHP minor version. Merged over the bundled list.
        'php_eol' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Audit (maintenance:audit)
    |--------------------------------------------------------------------------
    */

    'audit' => [
        'title' => 'Security Audit',
        'tag' => 'security:audit',

        'checks' => [
            Grafite\Maintenance\Checks\Audit\ComposerAudit::class,
            Grafite\Maintenance\Checks\Audit\NpmAudit::class,
            Grafite\Maintenance\Checks\Audit\AppConfig::class,
            Grafite\Maintenance\Checks\Audit\EnvDrift::class,
            Grafite\Maintenance\Checks\Audit\ExposedFiles::class,
        ],

        // Environments where debug mode and plain http are flagged.
        'production_environments' => ['production'],

        // Paths under public/ that should never be web accessible.
        'exposed_paths' => [
            '.env', '.env.backup', '.env.production', '.git', 'phpinfo.php', 'info.php', 'adminer.php',
        ],
    ],

];
