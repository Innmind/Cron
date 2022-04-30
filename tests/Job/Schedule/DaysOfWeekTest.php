<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\Job\Schedule\DaysOfWeek;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class DaysOfWeekTest extends TestCase
{
    use BlackBox;

    public function testEachDayOfWeek()
    {
        $schedule = DaysOfWeek::each();

        $this->assertInstanceOf(DaysOfWeek::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachDayOfWeekFromRawString()
    {
        $schedule = DaysOfWeek::of('*')->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(DaysOfWeek::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseDayOfWeekFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 6))
            ->then(function($minute) {
                $schedule = DaysOfWeek::of((string) $minute)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 6),
                Set\Integers::between(1, 6),
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute),
                );

                $schedule = DaysOfWeek::of($list)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 6),
                Set\Integers::between(0, 6),
            )
            ->then(function($from, $to) {
                $schedule = DaysOfWeek::of("$from-$to")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachDaysOfWeekteppedFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 6))
            ->then(function($step) {
                $schedule = DaysOfWeek::of("*/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfDaysOfWeekSteppedFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 6),
                Set\Integers::between(0, 6),
                Set\Integers::between(0, 6),
            )
            ->then(function($from, $to, $step) {
                $schedule = DaysOfWeek::of("$from-$to/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to/$step", $schedule->toString());
            });
    }

    public function testReturnNothingWhenUsingRandomString()
    {
        $this
            ->forAll(Set\Strings::any()->filter(static fn($value) => !\is_numeric($value)))
            ->then(function($value) {
                $schedule = DaysOfWeek::of($value)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertNull($schedule);
            });
    }
}
