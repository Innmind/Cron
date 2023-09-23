<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Job,
    Job\Schedule,
};
use Innmind\Server\Control\Server\Command;
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class JobTest extends TestCase
{
    use BlackBox;

    /**
     * @dataProvider commands
     */
    public function testStringCast($expected, $command)
    {
        $this->assertSame(
            "* * * * * $expected",
            (new Job(
                Schedule::everyMinute(),
                $command,
            ))->toString(),
        );
    }

    public function testReturnNothingWhenNotEnoughScheduleParts()
    {
        $this
            ->forAll(Set\Integers::between(0, 4))
            ->then(function($occurences) {
                $schedule = \implode(' ', \array_pad([], $occurences, '*'));

                $this->assertNull(Job::maybe("$schedule echo 'foo'")->match(
                    static fn($job) => $job,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingWhenNotEnoughSchedulePartsEvenThoughCommandContainsMoreThanSixParts()
    {
        $this->assertNull(Job::maybe('echo foo bar baz foobar foobaz barbaz')->match(
            static fn($job) => $job,
            static fn() => null,
        ));
    }

    public function testFromRawString()
    {
        $this
            ->forAll(
                Set\Integers::between(0, 59),
                Set\Integers::between(0, 23),
                Set\Integers::between(1, 31),
                Set\Integers::between(1, 12),
                Set\Integers::between(0, 6),
            )
            ->then(function($minute, $hour, $dayOfMonth, $month, $dayOfWeek) {
                $job = Job::maybe("$minute $hour $dayOfMonth $month $dayOfWeek echo foo bar baz")->match(
                    static fn($job) => $job,
                    static fn() => null,
                );

                $this->assertInstanceOf(Job::class, $job);
                $this->assertSame(
                    "$minute $hour $dayOfMonth $month $dayOfWeek echo foo bar baz",
                    $job->toString(),
                );
            });
    }

    public static function commands(): array
    {
        return [
            [
                "echo 'foo'",
                Command::foreground('echo')
                    ->withArgument('foo'),
            ],
            [
                "cd /tmp/watev && echo 'foo' > 'bar.txt'",
                Command::foreground('echo')
                    ->withArgument('foo')
                    ->overwrite(Path::of('bar.txt'))
                    ->withWorkingDirectory(Path::of('/tmp/watev')),
            ],
            [
                "FOO=bar BAR=baz cd /tmp && printenv > 'bar.txt'",
                Command::foreground('printenv')
                    ->overwrite(Path::of('bar.txt'))
                    ->withWorkingDirectory(Path::of('/tmp'))
                    ->withEnvironment('FOO', 'bar')
                    ->withEnvironment('BAR', 'baz'),
            ],
        ];
    }
}
