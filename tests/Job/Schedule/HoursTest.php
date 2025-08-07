<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\Job\Schedule\Hours;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class HoursTest extends TestCase
{
    use BlackBox;

    public function testEachHour()
    {
        $schedule = Hours::each();

        $this->assertInstanceOf(Hours::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachHourFromRawString()
    {
        $schedule = Hours::maybe('*')->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(Hours::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseHourFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(0, 23))
            ->then(function($minute) {
                $schedule = Hours::maybe((string) $minute)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfHoursFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 23),
                Set::integers()->between(1, 23),
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute),
                );

                $schedule = Hours::maybe($list)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfHoursFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 23),
                Set::integers()->between(0, 23),
            )
            ->then(function($from, $to) {
                $schedule = Hours::maybe("$from-$to")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachHourSteppedFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(0, 23))
            ->then(function($step) {
                $schedule = Hours::maybe("*/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfHoursSteppedFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 23),
                Set::integers()->between(0, 23),
                Set::integers()->between(0, 23),
            )
            ->then(function($from, $to, $step) {
                $schedule = Hours::maybe("$from-$to/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("$from-$to/$step", $schedule->toString());
            });
    }

    public function testReturnNothingWhenUsingRandomString()
    {
        $this
            ->forAll(
                Set::strings()
                    ->filter(static fn($value) => !\is_numeric($value))
                    ->filter(static fn($value) => $value !== '*'),
            )
            ->then(function($value) {
                $schedule = Hours::maybe($value)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertNull($schedule);
            });
    }
}
