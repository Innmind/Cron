<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Read,
    Job,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
    Server\Process\Output,
};
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class ReadTest extends TestCase
{
    public function testReadCrontabForConnectedUser()
    {
        $read = Read::forConnectedUser();
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return $command->toString() === "crontab '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($this->crontab());

        $jobs = $read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        );

        $this->assertInstanceOf(Sequence::class, $jobs);
        $this->assertCount(2, $jobs);
        $this->assertSame('1 2 3 4 5 echo foo', $jobs->first()->match(
            static fn($job) => $job->toString(),
            static fn() => null,
        ));
        $this->assertSame('2 3 4 5 6 echo bar', $jobs->last()->match(
            static fn($job) => $job->toString(),
            static fn() => null,
        ));
    }

    public function testReadCrontabForSpecificUser()
    {
        $read = Read::forUser('admin');
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return $command->toString() === "crontab '-u' 'admin' '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($this->crontab());

        $jobs = $read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        );

        $this->assertInstanceOf(Sequence::class, $jobs);
        $this->assertCount(2, $jobs);
        $this->assertSame('1 2 3 4 5 echo foo', $jobs->first()->match(
            static fn($job) => $job->toString(),
            static fn() => null,
        ));
        $this->assertSame('2 3 4 5 6 echo bar', $jobs->last()->match(
            static fn($job) => $job->toString(),
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenCrontabContainsInvalidJobs()
    {
        $read = Read::forUser('admin');
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return $command->toString() === "crontab '-u' 'admin' '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('toString')
            ->willReturn('*');

        $this->assertNull($read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        ));
    }

    private function crontab(): Output
    {
        $crontab = <<<CRONTAB
# First section
1 2 3 4 5 echo foo

# Second section
2 3 4 5 6 echo bar

CRONTAB;

        $output = $this->createMock(Output::class);
        $output
            ->expects($this->once())
            ->method('toString')
            ->willReturn($crontab);

        return $output;
    }
}
