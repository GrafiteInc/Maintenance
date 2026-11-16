<?php

namespace Grafite\Maintenance\Parsers;

class ComposerOutdated
{
    /**
     * Parse `composer outdated --format=json`, keeping packages with an update.
     *
     * @return array<int, array{name: string, current: string, latest: string, status: string}>|null null when the output is not valid JSON
     */
    public static function parse(string $json): ?array
    {
        $data = json_decode($json, true);

        if (! is_array($data) || ! array_key_exists('installed', $data)) {
            return null;
        }

        $packages = [];

        foreach ((array) $data['installed'] as $package) {
            if (! in_array($package['latest-status'] ?? null, ['update-possible', 'semver-safe-update'], true)) {
                continue;
            }

            $packages[] = [
                'name' => (string) $package['name'],
                'current' => (string) ($package['version'] ?? '?'),
                'latest' => (string) ($package['latest'] ?? '?'),
                'status' => (string) $package['latest-status'],
            ];
        }

        return $packages;
    }
}
