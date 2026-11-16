<?php

namespace Grafite\Maintenance\Support;

use Illuminate\Support\Str;

class Format
{
    /**
     * "0 packages", "1 package", "12 packages".
     */
    public static function count(int $count, string $unit): string
    {
        return $count . ' ' . Str::plural($unit, $count);
    }

    /**
     * Keep a list to $limit entries, noting how many were left out.
     */
    public static function limit(array $items, int $limit = 25): array
    {
        if (count($items) <= $limit) {
            return array_values($items);
        }

        $kept = array_slice(array_values($items), 0, $limit);
        $kept[] = '… and ' . (count($items) - $limit) . ' more';

        return $kept;
    }
}
