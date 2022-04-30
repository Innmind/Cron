<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\Job\Schedule\Hours;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
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
        $schedule = Hours::of('*')->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(Hours::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseHourFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 23))
            ->then(function($minute) {
                $schedule = Hours::of((string) $minute)->match(
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
                Set\Integers::between(0, 23),
                Set\Integers::between(1, 23),
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute),
                );

                $schedule = Hours::of($list)->match(
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
                Set\Integers::between(0, 23),
                Set\Integers::between(0, 23),
            )
            ->then(function($from, $to) {
                $schedule = Hours::of("$from-$to")->match(
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
            ->forAll(Set\Integers::between(0, 23))
            ->then(function($step) {
                $schedule = Hours::of("*/$step")->match(
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
                Set\Integers::between(0, 23),
                Set\Integers::between(0, 23),
                Set\Integers::between(0, 23),
            )
            ->then(function($from, $to, $step) {
                $schedule = Hours::of("$from-$to/$step")->match(
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
            ->forAll(Set\Strings::any()->filter(static fn($string) => !\is_numeric($string)))
            ->then(function($value) {
                $schedule = Hours::of($value)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertNull($schedule);
            });
    }
}
