<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job;

use Innmind\Cron\Job\Schedule;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};
use PHPUnit\Framework\Attributes\DataProvider;

class ScheduleTest extends TestCase
{
    use BlackBox;

    public function testStringCast()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
                Set::integers()->between(1, 31),
                Set::integers()->between(1, 12),
                Set::integers()->between(0, 6),
            )
            ->then(function($minute, $hour, $dayOfMonth, $month, $dayOfWeek) {
                $this->assertSame(
                    "$minute $hour $dayOfMonth $month $dayOfWeek",
                    (new Schedule(
                        Schedule\Minutes::at($minute),
                        Schedule\Hours::at($hour),
                        Schedule\DaysOfMonth::at($dayOfMonth),
                        Schedule\Months::at($month),
                        Schedule\DaysOfWeek::at($dayOfWeek),
                    ))->toString(),
                );
            });
    }

    #[DataProvider('schedules')]
    public function testScheduleFromRawString($value)
    {
        $schedule = Schedule::maybe($value)->match(
            static fn($schedule) => $schedule,
            static fn() => null,
        );

        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertSame($value, $schedule->toString());
    }

    public function testReturnNothingNotCorrectNumberOfParts()
    {
        $this
            ->forAll(Set::integers()->between(0, 100)->filter(static fn($int) => $int !== 5))
            ->then(function($int) {
                $string = \implode(' ', \array_pad([], $int, '*'));

                $this->assertNull(Schedule::maybe($string)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingWhenUsingRandomString()
    {
        $this
            ->forAll(Set\Strings::any())
            ->then(function($string) {
                $this->assertNull(Schedule::maybe($string)->match(
                    static fn($schedule) => $schedule,
                    static fn() => null,
                ));
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
            ->forAll(Set::integers()->between(0, 59))
            ->then(function($minute) {
                $schedule = Schedule::everyHourAt($minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute * * * *", $schedule->toString());
            });
    }

    public function testEveryDayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyDayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * *", $schedule->toString());
            });
    }

    public function testEveryMondayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyMondayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 1", $schedule->toString());
            });
    }

    public function testEveryTuesdayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyTuesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 2", $schedule->toString());
            });
    }

    public function testEveryWednesdayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyWednesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 3", $schedule->toString());
            });
    }

    public function testEveryThursdayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyThursdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 4", $schedule->toString());
            });
    }

    public function testEveryFridayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyFridayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 5", $schedule->toString());
            });
    }

    public function testEverySaturdayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySaturdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 6", $schedule->toString());
            });
    }

    public function testEverySundayAt()
    {
        $this
            ->forAll(
                Set::integers()->between(0, 59),
                Set::integers()->between(0, 23),
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySundayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 0", $schedule->toString());
            });
    }

    public static function schedules(): array
    {
        return [
            ['* * * * *'],
            ['1,59 1,23 1,31 1,12 0,6'],
            ['1-59 1-23 1-31 1-12 0-6'],
            ['1-59/2 1-23/2 1-31/2 1-12/2 0-6/2'],
        ];
    }
}
