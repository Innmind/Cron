<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\Job\Schedule\Minutes;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class MinutesTest extends TestCase
{
    use BlackBox;

    public function testEachMinute()
    {
        $schedule = Minutes::each();

        $this->assertInstanceOf(Minutes::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachMinuteFromRawString()
    {
        $schedule = Minutes::maybe('*')->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(Minutes::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseMinuteFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(0, 59))
            ->then(function($minute) {
                $schedule = Minutes::maybe((string) $minute)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Minutes::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfMinutesFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(1, 59),
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute),
                );

                $schedule = Minutes::maybe($list)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Minutes::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfMinutesFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 59),
            )
            ->then(function($from, $to) {
                $schedule = Minutes::maybe("$from-$to")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Minutes::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachMinuteSteppedFromRawString()
    {
        $this
            ->forAll(Set::integers()->between(0, 59))
            ->then(function($step) {
                $schedule = Minutes::maybe("*/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Minutes::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfMinutesSteppedFromRawString()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 59),
            )
            ->then(function($from, $to, $step) {
                $schedule = Minutes::maybe("$from-$to/$step")->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertInstanceOf(Minutes::class, $schedule);
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
                $schedule = Minutes::maybe($value)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                );

                $this->assertNull($schedule);
            });
    }
}
