<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Months
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-pure
     *
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
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(string $value): Maybe
    {
        return Maybe::just($value)
            ->filter(new Range('(0?[1-9]|1[0-2])'))
            ->map(static fn($value) => new self($value));
    }

    /**
     * @psalm-pure
     */
    public static function each(): self
    {
        return new self('*');
    }

    /**
     * @psalm-pure
     *
     * @param int<1, 12> $month
     */
    public static function at(int $month): self
    {
        return new self((string) $month);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
