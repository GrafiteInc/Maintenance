<?php

namespace Grafite\Maintenance\Parsers;

class NpmOutdated
{
    /**
     * Parse `npm outdated --json`. npm prints "{}" (or nothing) when everything is current.
     *
     * @return array<int, array{name: string, current: string, latest: string}>|null null when the output is not valid JSON
     */
    public static function parse(string $json): ?array
    {
        if (trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);

        if (! is_array($data) || isset($data['error'])) {
            return null;
        }

        $packages = [];

        foreach ($data as $name => $details) {
            // Workspaces can report the same dependency more than once.
            $details = array_is_list($details) ? ($details[0] ?? []) : $details;

            $packages[] = [
                'name' => (string) $name,
                'current' => (string) ($details['current'] ?? 'not installed'),
                'latest' => (string) ($details['latest'] ?? '?'),
            ];
        }

        return $packages;
    }
}
