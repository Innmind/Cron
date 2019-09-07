<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron\Job;

use Innmind\Cron\{
    Job\Schedule,
    Exception\DomainException
};
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class ScheduleTest extends TestCase
{
    use TestTrait;

    public function testStringCast()
    {
        $this
            ->forAll(
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23)),
                Generator\elements(...range(1, 31)),
                Generator\elements(...range(1, 12)),
                Generator\elements(...range(0, 6))
            )
            ->then(function($minute, $hour, $dayOfMonth, $month, $dayOfWeek) {
                $this->assertSame(
                    "$minute $hour $dayOfMonth $month $dayOfWeek",
                    (string) new Schedule(
                        Schedule\Minutes::of((string) $minute),
                        Schedule\Hours::of((string) $hour),
                        Schedule\DaysOfMonth::of((string) $dayOfMonth),
                        Schedule\Months::of((string) $month),
                        Schedule\DaysOfWeek::of((string) $dayOfWeek)
                    )
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
        $this->assertSame($value, (string) $schedule);
    }

    public function testThrowWhenNotCorrectNumberOfParts()
    {
        $this
            ->forAll(Generator\pos())
            ->when(static function($int) {
                return $int !== 5;
            })
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
            ->forAll(Generator\string())
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
        $this->assertSame('* * * * *', (string) $schedule);
    }

    public function testEveryHourAt()
    {
        $this
            ->forAll(Generator\elements(...range(0, 59)))
            ->then(function($minute) {
                $schedule = Schedule::everyHourAt($minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute * * * *", (string) $schedule);
            });
    }

    public function testThrowWhenEveryHourAtInvalidMinute()
    {
        $this
            ->forAll(Generator\pos())
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyDayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * *", (string) $schedule);
            });
    }

    public function testThrowWhenEveryDayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyMondayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 0", (string) $schedule);
            });
    }

    public function testThrowWhenEveryMondayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyTuesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 1", (string) $schedule);
            });
    }

    public function testThrowWhenEveryTuesdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyWednesdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 2", (string) $schedule);
            });
    }

    public function testThrowWhenEveryWednesdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyThursdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 3", (string) $schedule);
            });
    }

    public function testThrowWhenEveryThursdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everyFridayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 4", (string) $schedule);
            });
    }

    public function testThrowWhenEveryFridayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySaturdayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 5", (string) $schedule);
            });
    }

    public function testThrowWhenEverySaturdayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\elements(...range(0, 23))
            )
            ->then(function($minute, $hour) {
                $schedule = Schedule::everySundayAt($hour, $minute);

                $this->assertInstanceOf(Schedule::class, $schedule);
                $this->assertSame("$minute $hour * * 6", (string) $schedule);
            });
    }

    public function testThrowWhenEverySundayAtInvalidMinute()
    {
        $this
            ->forAll(
                Generator\pos(),
                Generator\elements(...range(0, 23))
            )
            ->when(static function($minute) {
                return $minute > 59;
            })
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
                Generator\elements(...range(0, 59)),
                Generator\pos()
            )
            ->when(static function($minute, $hour) {
                return $hour > 23;
            })
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
