<?php

namespace Grafite\Maintenance\Checks;

enum Status: string
{
    case Ok = 'ok';
    case Unknown = 'unknown';
    case Warning = 'warning';
    case Critical = 'critical';

    public function severity(): int
    {
        return match ($this) {
            self::Ok => 0,
            self::Unknown => 1,
            self::Warning => 2,
            self::Critical => 3,
        };
    }

    public function worse(self $other): self
    {
        return $other->severity() > $this->severity() ? $other : $this;
    }

    /**
     * Grade a value against ['warning' => x, 'critical' => y] (value >= threshold).
     */
    public static function fromThreshold(float $value, array $threshold): self
    {
        if (isset($threshold['critical']) && $value >= $threshold['critical']) {
            return self::Critical;
        }

        if (isset($threshold['warning']) && $value >= $threshold['warning']) {
            return self::Warning;
        }

        return self::Ok;
    }

    public static function worst(iterable $statuses): self
    {
        $worst = self::Ok;

        foreach ($statuses as $status) {
            $worst = $worst->worse($status);
        }

        return $worst;
    }
}
