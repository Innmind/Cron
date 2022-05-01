<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Immutable\Maybe;

final class DaysOfMonth
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param literal-string $value
     *
     * @throws \DomainException
     */
    public static function of(string $value): self
    {
        return self::maybe($value)->match(
            static fn($self) => $self,
            static fn() => throw new \DomainException($value),
        );
    }

    /**
     * @return Maybe<self>
     */
    public static function maybe(string $value): Maybe
    {
        return Maybe::just($value)
            ->filter(new Range('([0-2]?[0-9]|30|31)'))
            ->map(static fn($value) => new self($value));
    }

    public static function each(): self
    {
        return new self('*');
    }

    /**
     * @param int<1, 31> $day
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
