<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Job,
    Job\Schedule,
    Exception\DomainException,
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
            "1 2 3 4 5 $expected",
            (new Job(
                Schedule::of('1 2 3 4 5'),
                $command
            ))->toString(),
        );
    }

    public function testThrowWhenNotEnoughScheduleParts()
    {
        $this
            ->forAll(Set\Integers::between(0, 4))
            ->then(function($occurences) {
                $schedule = \implode(' ', \array_pad([], $occurences, '*'));

                $this->expectException(DomainException::class);
                $this->expectExceptionMessage("$schedule echo 'foo'");

                Job::of("$schedule echo 'foo'");
            });
    }

    public function testThrowWhenNotEnoughSchedulePartsEvenThoughCommandContainsMoreThanSixParts()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('echo foo bar baz foobar foobaz barbaz');

        Job::of('echo foo bar baz foobar foobaz barbaz');
    }

    public function testFromRawString()
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
                $job = Job::of("$minute $hour $dayOfMonth $month $dayOfWeek echo foo bar baz");

                $this->assertInstanceOf(Job::class, $job);
                $this->assertSame(
                    "$minute $hour $dayOfMonth $month $dayOfWeek echo foo bar baz",
                    $job->toString(),
                );
            });
    }

    public function commands(): array
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
