<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\Job\Schedule\Months;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class MonthsTest extends TestCase
{
    use BlackBox;

    public function testEachMonth()
    {
        $schedule = Months::each();

        $this->assertInstanceOf(Months::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachMonthFromRawString()
    {
        $schedule = Months::maybe('*')->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(Months::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseMonthFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(1, 12))
            ->then(function($minute) {
                $schedule = Months::maybe((string) $minute)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfMonthsFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(1, 12),
                Set::integers()->between(1, 12),
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute),
                );

                $schedule = Months::maybe($list)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfMonthsFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(1, 12),
                Set::integers()->between(1, 12),
            )
            ->then(function($from, $to) {
                $schedule = Months::maybe("$from-$to")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachMonthSteppedFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(1, 12))
            ->then(function($step) {
                $schedule = Months::maybe("*/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfMonthsSteppedFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(1, 12),
                Set::integers()->between(1, 12),
                Set::integers()->between(1, 12),
            )
            ->then(function($from, $to, $step) {
                $schedule = Months::maybe("$from-$to/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame("$from-$to/$step", $schedule->toString());
            });
    }

    public function testReturnNothingWhenUsingRandomString()
    {
        $this
            ->forAll(Set::strings()->filter(static fn($value) => !\is_numeric($value)))
            ->then(function($value) {
                $schedule = Months::maybe($value)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertNull($schedule);
            });
    }
}
