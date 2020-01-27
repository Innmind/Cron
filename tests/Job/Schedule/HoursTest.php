<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job\Schedule;

use Innmind\Cron\{
    Job\Schedule\Hours,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class HoursTest extends TestCase
{
    use TestTrait;

    public function testEachHour()
    {
        $schedule = Hours::each();

        $this->assertInstanceOf(Hours::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testEachHourFromRawString()
    {
        $schedule = Hours::of('*');

        $this->assertInstanceOf(Hours::class, $schedule);
        $this->assertSame('*', $schedule->toString());
    }

    public function testPreciseHourFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 23)))
            ->then(function($minute) {
                $schedule = Hours::of((string) $minute);

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame((string) $minute, $schedule->toString());
            });
    }

    public function testListOfHoursFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 23)),
                Generator\elements(...range(1, 23))
            )
            ->then(function($minute, $occurences) {
                $list = implode(
                    ',',
                    array_pad([], $occurences, $minute)
                );

                $schedule = Hours::of($list);

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame($list, $schedule->toString());
            });
    }

    public function testRangeOfHoursFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 23)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($from, $to) {
                $schedule = Hours::of("$from-$to");

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("$from-$to", $schedule->toString());
            });
    }

    public function testEachHourSteppedFromRawString()
    {
        $this
            ->forAll(Generator\elements(...range(0, 23)))
            ->then(function($step) {
                $schedule = Hours::of("*/$step");

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("*/$step", $schedule->toString());
            });
    }

    public function testRangeOfHoursSteppedFromRawString()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 23)),
                Generator\elements(...range(0, 23)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($from, $to, $step) {
                $schedule = Hours::of("$from-$to/$step");

                $this->assertInstanceOf(Hours::class, $schedule);
                $this->assertSame("$from-$to/$step", $schedule->toString());
            });
    }

    public function testThrowExceptionWhenUsingRandomString()
    {
        $this
            ->forAll(Generator\string())
            ->then(function($value) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($value);

                Hours::of($value);
            });
    }
}
