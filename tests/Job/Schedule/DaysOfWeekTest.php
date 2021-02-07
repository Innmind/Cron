<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\DaysOfWeek,
    Exception\DomainException,
};
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
        $schedule = DaysOfWeek::of('*');

        $this->assertInstanceOf(DaysOfWeek::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseDayOfWeekFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 6))
            ->then(function($minute) {
                $schedule = DaysOfWeek::of((string) $minute);

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 6),
                Set\Integers::between(1, 6)
            )
            ->then(function($minute, $occurences) {
                $list = implode(
                    ',',
                    array_pad([], $occurences, $minute)
                );

                $schedule = DaysOfWeek::of($list);

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 6),
                Set\Integers::between(0, 6)
            )
            ->then(function($from, $to) {
                $schedule = DaysOfWeek::of("$from-$to");

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachDaysOfWeekteppedFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 6))
            ->then(function($step) {
                $schedule = DaysOfWeek::of("*/$step");

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
                Set\Integers::between(0, 6)
            )
            ->then(function($from, $to, $step) {
                $schedule = DaysOfWeek::of("$from-$to/$step");

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to/$step", $schedule->toString());
            });
    }

    public function testThrowExceptionWhenUsingRandomString()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function($value) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($value);

                DaysOfWeek::of($value);
            });
    }
}
