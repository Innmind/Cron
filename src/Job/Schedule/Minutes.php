<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Immutable\Maybe;

final class Minutes
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
            ->filter(new Range('[0-5]?[0-9]'))
            ->map(static fn($value) => new self($value));
    }

    public static function each(): self
    {
        return new self('*');
    }

    /**
     * @param int<0, 59> $minute
     */
    public static function at(int $minute): self
    {
        return new self((string) $minute);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
