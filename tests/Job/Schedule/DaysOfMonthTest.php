<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\DaysOfMonth,
    Exception\DomainException
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class DaysOfMonthTest extends TestCase
{
    use TestTrait;

    public function testEachDayOfMonth()
    {
        $schedule = DaysOfMonth::each();

        $this->assertInstanceOf(DaysOfMonth::class, $schedule);
        $this->assertSame('*', (string) $schedule);
    }

    public function testEachDayOfMonthFromRawString()
    {
        $schedule = DaysOfMonth::of('*');

        $this->assertInstanceOf(DaysOfMonth::class, $schedule);
        $this->assertSame('*', (string) $schedule);
    }

    public function testPreciseDayOfMonthFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 31)))
            ->then(function($minute) {
                $schedule = DaysOfMonth::of((string) $minute);

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame((string) $minute, (string) $schedule);
            });
    }

    public function testListOfDaysOfMonthFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 31)),
                Generator\elements(...range(1, 31))
            )
            ->then(function($minute, $occurences) {
                $list = implode(
                    ',',
                    array_pad([], $occurences, $minute)
                );

                $schedule = DaysOfMonth::of($list);

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame($list, (string) $schedule);
            });
    }

    public function testRangeOfDaysOfMonthFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 31)),
                Generator\elements(...range(0, 31))
            )
            ->then(function($from, $to) {
                $schedule = DaysOfMonth::of("$from-$to");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame("$from-$to", (string) $schedule);
            });
    }

    public function testEachDaysOfMonthteppedFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 31)))
            ->then(function($step) {
                $schedule = DaysOfMonth::of("*/$step");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame("*/$step", (string) $schedule);
            });
    }

    public function testRangeOfDaysOfMonthSteppedFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 31)),
                Generator\elements(...range(0, 31)),
                Generator\elements(...range(0, 31))
            )
            ->then(function($from, $to, $step) {
                $schedule = DaysOfMonth::of("$from-$to/$step");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
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

                DaysOfMonth::of($value);
            });
    }
}
