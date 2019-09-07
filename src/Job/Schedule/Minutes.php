<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Cron\Exception\DomainException;
use Innmind\Immutable\Str;

final class Minutes
{
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function of(string $value): self
    {
        if ($value === '*') {
            return self::each();
        }

        $value = Str::of($value);

        // precise value
        if ($value->matches('~^[0-5]?[0-9]$~')) {
            return new self((string) $value);
        }

        // list
        if ($value->matches('~^[0-5]?[0-9](,[0-5]?[0-9])+$~')) {
            return new self((string) $value);
        }

        // range
        if ($value->matches('~^[0-5]?[0-9]-[0-5]?[0-9]$~')) {
            return new self((string) $value);
        }

        // stepped
        if ($value->contains('/')) {
            [$minutes, $step] = $value->split('/');

            // validate $minutes format
            self::of((string) $minutes);

            if ($step->matches('~^[0-5]?[0-9]$~')) {
                return new self((string) $value);
            }
        }

        throw new DomainException((string) $value);
    }

    public static function each(): self
    {
        return new self('*');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
