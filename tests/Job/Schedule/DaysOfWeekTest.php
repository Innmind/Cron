<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\DaysOfWeek,
    Exception\DomainException
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class DaysOfWeekTest extends TestCase
{
    use TestTrait;

    public function testEachDayOfWeek()
    {
        $schedule = DaysOfWeek::each();

        $this->assertInstanceOf(DaysOfWeek::class, $schedule);
        $this->assertSame('*', (string) $schedule);
    }

    public function testEachDayOfWeekFromRawString()
    {
        $schedule = DaysOfWeek::of('*');

        $this->assertInstanceOf(DaysOfWeek::class, $schedule);
        $this->assertSame('*', (string) $schedule);
    }

    public function testPreciseDayOfWeekFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 6)))
            ->then(function($minute) {
                $schedule = DaysOfWeek::of((string) $minute);

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame((string) $minute, (string) $schedule);
            });
    }

    public function testListOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 6)),
                Generator\elements(...range(1, 6))
            )
            ->then(function($minute, $occurences) {
                $list = implode(
                    ',',
                    array_pad([], $occurences, $minute)
                );

                $schedule = DaysOfWeek::of($list);

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame($list, (string) $schedule);
            });
    }

    public function testRangeOfDaysOfWeekFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 6)),
                Generator\elements(...range(0, 6))
            )
            ->then(function($from, $to) {
                $schedule = DaysOfWeek::of("$from-$to");

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to", (string) $schedule);
            });
    }

    public function testEachDaysOfWeekteppedFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 6)))
            ->then(function($step) {
                $schedule = DaysOfWeek::of("*/$step");

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("*/$step", (string) $schedule);
            });
    }

    public function testRangeOfDaysOfWeekSteppedFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 6)),
                Generator\elements(...range(0, 6)),
                Generator\elements(...range(0, 6))
            )
            ->then(function($from, $to, $step) {
                $schedule = DaysOfWeek::of("$from-$to/$step");

                $this->assertInstanceOf(DaysOfWeek::class, $schedule);
                $this->assertSame("$from-$to/$step", (string) $schedule);
            });
    }

    public function testThrowExceptionWhenUsingRandomString()
    {
        $this
            ->forAll(Generator\string())
            ->then(function($value) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($value);

                DaysOfWeek::of($value);
            });
    }
}
