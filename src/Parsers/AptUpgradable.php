<?php

namespace Grafite\Maintenance\Parsers;

class AptUpgradable
{
    /**
     * Parse `apt list --upgradable` output.
     *
     * Lines look like:
     *   openssl/jammy-updates,jammy-security 3.0.2-0ubuntu1.15 amd64 [upgradable from: 3.0.2-0ubuntu1.14]
     *
     * @return array{total: int, security: int, packages: array<int, array{name: string, from: ?string, to: string, security: bool}>}
     */
    public static function parse(string $output): array
    {
        $packages = [];

        foreach (preg_split('/\R/', trim($output)) as $line) {
            $line = trim($line);

            if (! preg_match('/^([^\s\/]+)\/(\S+)\s+(\S+)/', $line, $matches)) {
                continue;
            }

            preg_match('/upgradable from:\s*([^\]]+)\]/', $line, $from);

            $packages[] = [
                'name' => $matches[1],
                'from' => isset($from[1]) ? trim($from[1]) : null,
                'to' => $matches[3],
                'security' => str_contains($matches[2], '-security'),
            ];
        }

        return [
            'total' => count($packages),
            'security' => count(array_filter($packages, fn ($package) => $package['security'])),
            'packages' => $packages,
        ];
    }
}
