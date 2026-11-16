<?php

namespace Grafite\Maintenance\Checks;

final class CheckResult
{
    public function __construct(
        public readonly Status $status,
        public readonly string $summary,
        public readonly array $details = [],
    ) {
    }

    public static function ok(string $summary, array $details = []): self
    {
        return new self(Status::Ok, $summary, $details);
    }

    public static function warning(string $summary, array $details = []): self
    {
        return new self(Status::Warning, $summary, $details);
    }

    public static function critical(string $summary, array $details = []): self
    {
        return new self(Status::Critical, $summary, $details);
    }

    public static function unknown(string $summary, array $details = []): self
    {
        return new self(Status::Unknown, $summary, $details);
    }

    public function toArray(): array
    {
        $result = [
            'status' => $this->status->value,
            'summary' => $this->summary,
        ];

        if (! empty($this->details)) {
            $result['details'] = self::normalize($this->details);
        }

        return $result;
    }

    /**
     * Make values safe for Mission Control's JSON table renderer, which shows
     * any empty() value (0, "0", "", []) as "N/A". Checks should format numbers
     * with units ("0 jobs", "0%"); this is the safety net for bare values.
     */
    public static function normalize(array $values): array
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $normalized[$key] = match (true) {
                is_array($value) => empty($value) ? 'none' : self::normalize($value),
                is_bool($value), is_null($value) => $value,
                is_int($value), is_float($value) => $value == 0 ? 'none' : (string) $value,
                $value instanceof \Stringable => (string) $value,
                default => in_array((string) $value, ['', '0'], true) ? 'none' : (string) $value,
            };
        }

        return $normalized;
    }
}
