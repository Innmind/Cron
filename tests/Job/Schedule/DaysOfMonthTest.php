<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\DaysOfMonth,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class DaysOfMonthTest extends TestCase
{
    use BlackBox;

    public function testEachDayOfMonth()
    {
        $schedule = DaysOfMonth::each();

        $this->assertInstanceOf(DaysOfMonth::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachDayOfMonthFromRawString()
    {
        $schedule = DaysOfMonth::of('*');

        $this->assertInstanceOf(DaysOfMonth::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseDayOfMonthFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 31))
            ->then(function($minute) {
                $schedule = DaysOfMonth::of((string) $minute);

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfDaysOfMonthFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 31),
                Set\Integers::between(1, 31)
            )
            ->then(function($minute, $occurences) {
                $list = implode(
                    ',',
                    array_pad([], $occurences, $minute)
                );

                $schedule = DaysOfMonth::of($list);

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfDaysOfMonthFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 31),
                Set\Integers::between(0, 31)
            )
            ->then(function($from, $to) {
                $schedule = DaysOfMonth::of("$from-$to");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachDaysOfMonthteppedFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(0, 31))
            ->then(function($step) {
                $schedule = DaysOfMonth::of("*/$step");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfDaysOfMonthSteppedFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 31),
                Set\Integers::between(0, 31),
                Set\Integers::between(0, 31)
            )
            ->then(function($from, $to, $step) {
                $schedule = DaysOfMonth::of("$from-$to/$step");

                $this->assertInstanceOf(DaysOfMonth::class, $schedule);
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

                DaysOfMonth::of($value);
            });
    }
}
