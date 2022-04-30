<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Immutable\Maybe;

final class DaysOfWeek
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return Maybe<self>
     */
    public static function of(string $value): Maybe
    {
        return Maybe::just($value)
            ->filter(new Range('[0-6]'))
            ->map(static fn($value) => new self($value));
    }

    public static function each(): self
    {
        return new self('*');
    }

    /**
     * @param int<0, 6> $day
     */
    public static function at(int $day): self
    {
        return new self((string) $day);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
