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

    /**
     * @return Maybe<self>
     */
    public static function of(string $value): Maybe
    {
        $parts = Str::of($value)->split(' ');

        if ($parts->size() !== 5) {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        $minutes = $parts
            ->get(0)
            ->flatMap(static fn($value) => Minutes::of($value->toString()));
        $hours = $parts
            ->get(1)
            ->flatMap(static fn($value) => Hours::of($value->toString()));
        $daysOfMonth = $parts
            ->get(2)
            ->flatMap(static fn($value) => DaysOfMonth::of($value->toString()));
        $months = $parts
            ->get(3)
            ->flatMap(static fn($value) => Months::of($value->toString()));
        $daysOfWeek = $parts
            ->get(4)
            ->flatMap(static fn($value) => DaysOfWeek::of($value->toString()));

        return Maybe::all($minutes, $hours, $daysOfMonth, $months, $daysOfWeek)
            ->map(static fn(
                Minutes $minutes,
                Hours $hours,
                DaysOfMonth $daysOfMonth,
                Months $months,
                DaysOfWeek $daysOfWeek,
            ) => new self(
                $minutes,
                $hours,
                $daysOfMonth,
                $months,
                $daysOfWeek,
            ));
    }

    public static function everyMinute(): self
    {
        return new self(
            Minutes::each(),
            Hours::each(),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::each(),
        );
    }

    /**
     * @param int<0, 59> $minute
     */
    public static function everyHourAt(int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::each(),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::each(),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyDayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::each(),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyMondayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(0),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyTuesdayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(1),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyWednesdayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(2),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyThursdayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(3),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everyFridayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(4),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everySaturdayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(5),
        );
    }

    /**
     * @param int<0, 23> $hour
     * @param int<0, 59> $minute
     */
    public static function everySundayAt(int $hour, int $minute): self
    {
        return new self(
            Minutes::at($minute),
            Hours::at($hour),
            DaysOfMonth::each(),
            Months::each(),
            DaysOfWeek::at(6),
        );
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
