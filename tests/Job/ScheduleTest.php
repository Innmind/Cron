<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job;

use Innmind\Cron\{
    Job\Schedule,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ScheduleTest extends TestCase
{
    use BlackBox;

    public function testStringCast()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23),
                Set\Integers::between(1, 31),
                Set\Integers::between(1, 12),
                Set\Integers::between(0, 6)
            )
            ->then(function($minute, $hour, $dayOfMonth, $month, $dayOfWeek) {
                $this->assertSame(
                    "$minute $hour $dayOfMonth $month $dayOfWeek",
                    (new Schedule(
                        Schedule\Minutes::of((string) $minute),
                        Schedule\Hours::of((string) $hour),
                        Schedule\DaysOfMonth::of((string) $dayOfMonth),
                        Schedule\Months::of((string) $month),
                        Schedule\DaysOfWeek::of((string) $dayOfWeek)
                    ))->toString(),
                );
            });
    }

    /**
     * @dataProvider schedules
     */
    public function testScheduleFromRawString($value)
    {
        $schedule = Schedule::of($value);

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame($value, $schedule->toString());
    }

    public function testThrowWhenNotCorrectNumberOfParts()
    {
        $this
            ->forAll(Set\Integers::between(0, 100)->filter(static fn($int) => $int !== 5))
            ->then(function($int) {
                $string = implode(' ', array_pad([], $int, '*'));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                Schedule::of($string);
            });
    }

    public function testThrowWhenUsingRandomString()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function($string) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage($string);

                Schedule::of($string);
            });
    }

    public function testEveryMinute()
    {
        $schedule = Schedule::everyMinute();

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame('* * * * *', $schedule->toString());
    }

    public function testEveryHourAt()
    {
        $this
            ->forAll(Set\Integers::between(0, 59))
            ->then(function($minute) {
                $schedule = Schedule::everyHourAt($minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute * * * *", $schedule->toString());
            });
    }

    public function testThrowWhenEveryHourAtInvalidMinute()
    {
        $this
            ->forAll(Set\Integers::above(60))
            ->then(function($minute) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute * * * *");

                Schedule::everyHourAt($minute);
            });
    }

    public function testEveryDayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyDayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * *", $schedule->toString());
            });
    }

    public function testThrowWhenEveryDayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * *");

                Schedule::everyDayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryDayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24),
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * *");

                Schedule::everyDayAt($hour, $minute);
            });
    }

    public function testEveryMondayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyMondayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 0", $schedule->toString());
            });
    }

    public function testThrowWhenEveryMondayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 0");

                Schedule::everyMondayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryMondayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24),
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 0");

                Schedule::everyMondayAt($hour, $minute);
            });
    }

    public function testEveryTuesdayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyTuesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 1", $schedule->toString());
            });
    }

    public function testThrowWhenEveryTuesdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 1");

                Schedule::everyTuesdayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryTuesdayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24),
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 1");

                Schedule::everyTuesdayAt($hour, $minute);
            });
    }

    public function testEveryWednesdayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyWednesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 2", $schedule->toString());
            });
    }

    public function testThrowWhenEveryWednesdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 2");

                Schedule::everyWednesdayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryWednesdayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 2");

                Schedule::everyWednesdayAt($hour, $minute);
            });
    }

    public function testEveryThursdayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyThursdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 3", $schedule->toString());
            });
    }

    public function testThrowWhenEveryThursdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 3");

                Schedule::everyThursdayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryThursdayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 3");

                Schedule::everyThursdayAt($hour, $minute);
            });
    }

    public function testEveryFridayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyFridayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 4", $schedule->toString());
            });
    }

    public function testThrowWhenEveryFridayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 4");

                Schedule::everyFridayAt($hour, $minute);
            });
    }

    public function testThrowWhenEveryFridayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 4");

                Schedule::everyFridayAt($hour, $minute);
            });
    }

    public function testEverySaturdayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySaturdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 5", $schedule->toString());
            });
    }

    public function testThrowWhenEverySaturdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 5");

                Schedule::everySaturdayAt($hour, $minute);
            });
    }

    public function testThrowWhenEverySaturdayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24),
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 5");

                Schedule::everySaturdayAt($hour, $minute);
            });
    }

    public function testEverySundayAt()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySundayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 6", $schedule->toString());
            });
    }

    public function testThrowWhenEverySundayAtInvalidMinute()
    {
        $this
            ->forAll(
                Set\Integers::above(60),
                Set\Integers::between(0, 23)
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 6");

                Schedule::everySundayAt($hour, $minute);
            });
    }

    public function testThrowWhenEverySundayAtInvalidHour()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::above(24),
            )
            ->then(function($minute, $hour) {
                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$minute $hour * * 6");

                Schedule::everySundayAt($hour, $minute);
            });
    }

    public function schedules(): array
    {
        return [
            ['* * * * *'],
            ['1,59 1,23 1,31 1,12 0,6'],
            ['1-59 1-23 1-31 1-12 0-6'],
            ['1-59/2 1-23/2 1-31/2 1-12/2 0-6/2'],
        ];
    }
}
