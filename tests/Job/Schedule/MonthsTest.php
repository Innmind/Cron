<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\Months,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
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
        $schedule = Months::of('*');

        $this->assertInstanceOf(Months::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseMonthFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(1, 12))
            ->then(function($minute) {
                $schedule = Months::of((string) $minute);

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfMonthsFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(1, 12),
                Set\Integers::between(1, 12)
            )
            ->then(function($minute, $occurences) {
                $list = \implode(
                    ',',
                    \array_pad([], $occurences, $minute)
                );

                $schedule = Months::of($list);

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfMonthsFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(1, 12),
                Set\Integers::between(1, 12)
            )
            ->then(function($from, $to) {
                $schedule = Months::of("$from-$to");

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachMonthSteppedFromRawString()
    {
        $this
            ->forAll(Set\Integers::between(1, 12))
            ->then(function($step) {
                $schedule = Months::of("*/$step");

                $this->assertInstanceOf(Months::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfMonthsSteppedFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(1, 12),
                Set\Integers::between(1, 12),
                Set\Integers::between(1, 12)
            )
            ->then(function($from, $to, $step) {
                $schedule = Months::of("$from-$to/$step");

                $this->assertInstanceOf(Months::class, $schedule);
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

                Months::of($value);
            });
    }
}
