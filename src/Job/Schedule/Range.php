<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Range
{
    private string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function __invoke(string $value): bool
    {
        if ($value === '*') {
            return true;
        }

        $value = Str::of($value);

        // precise value
        if ($value->matches("~^{$this->pattern}$~")) {
            return true;
        }

        // list
        if ($value->matches("~^{$this->pattern}(,{$this->pattern})+$~")) {
            return true;
        }

        // range
        if ($value->matches("~^{$this->pattern}-{$this->pattern}$~")) {
            return true;
        }

        // stepped
        if ($value->contains('/')) {
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            [$hours, $step] = $value->split('/')->toList();

            // validate $hours format
            if (!$this($hours->toString())) {
                return false;
            }

            if ($step->matches("~^{$this->pattern}$~")) {
                return true;
            }
        }

        return false;
    }
}
