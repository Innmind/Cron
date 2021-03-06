<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Cron\Exception\DomainException;
use Innmind\Immutable\Str;
use function Innmind\Immutable\unwrap;

final class Range
{
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @throws DomainException When the value is not accepted in the range
     */
    public function __invoke(string $value): void
    {
        if ($value === '*') {
            return;
        }

        $value = Str::of($value);

        // precise value
        if ($value->matches("~^{$this->pattern}$~")) {
            return;
        }

        // list
        if ($value->matches("~^{$this->pattern}(,{$this->pattern})+$~")) {
            return;
        }

        // range
        if ($value->matches("~^{$this->pattern}-{$this->pattern}$~")) {
            return;
        }

        // stepped
        if ($value->contains('/')) {
            [$hours, $step] = unwrap($value->split('/'));

            // validate $hours format
            $this($hours->toString());

            if ($step->matches("~^{$this->pattern}$~")) {
                return;
            }
        }

        throw new DomainException($value->toString());
    }
}
