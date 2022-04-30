<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job;

use Innmind\Cron\{
    Job\Schedule\Minutes,
    Job\Schedule\Hours,
    Job\Schedule\DaysOfMonth,
    Job\Schedule\Months,
    Job\Schedule\DaysOfWeek,
    Exception\DomainException,
};
use Innmind\Immutable\{
    Str,
    Maybe,
};

final class Schedule
{
    private Minutes $minutes;
    private Hours $hours;
    private DaysOfMonth $daysOfMonth;
    private Months $months;
    private DaysOfWeek $daysOfWeek;

    public function __construct(
        Minutes $minutes,
        Hours $hours,
        DaysOfMonth $daysOfMonth,
        Months $months,
        DaysOfWeek $daysOfWeek,
    ) {
        $this->minutes = $minutes;
        $this->hours = $hours;
        $this->daysOfMonth = $daysOfMonth;
        $this->months = $months;
        $this->daysOfWeek = $daysOfWeek;
    }

    public static function of(string $value): self
    {
        $parts = Str::of($value)->split(' ');

        try {
            return Maybe::all($parts->get(0), $parts->get(1), $parts->get(2), $parts->get(3), $parts->get(4))
                ->map(static fn(Str $minutes, Str $hours, Str $daysOfMonth, Str $months, Str $daysOfWeek) => new self(
                    Minutes::of($minutes->toString()),
                    Hours::of($hours->toString()),
                    DaysOfMonth::of($daysOfMonth->toString()),
                    Months::of($months->toString()),
                    DaysOfWeek::of($daysOfWeek->toString()),
                ))
                ->match(
                    static fn($self) => $self,
                    static fn() => throw new DomainException($value),
                );
        } catch (DomainException $e) {
            throw new DomainException($value);
        }
    }

    public static function everyMinute(): self
    {
        return self::of('* * * * *');
    }

    public static function everyHourAt(int $minute): self
    {
        return self::of("$minute * * * *");
    }

    public static function everyDayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * *");
    }

    public static function everyMondayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 0");
    }

    public static function everyTuesdayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 1");
    }

    public static function everyWednesdayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 2");
    }

    public static function everyThursdayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 3");
    }

    public static function everyFridayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 4");
    }

    public static function everySaturdayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 5");
    }

    public static function everySundayAt(int $hour, int $minute): self
    {
        return self::of("$minute $hour * * 6");
    }

    public function toString(): string
    {
        return \sprintf(
            '%s %s %s %s %s',
            $this->minutes->toString(),
            $this->hours->toString(),
            $this->daysOfMonth->toString(),
            $this->months->toString(),
            $this->daysOfWeek->toString(),
        );
    }
}
