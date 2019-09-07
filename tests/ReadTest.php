<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Read,
    Job,
    Exception\UnableToReadCrontab,
    Exception\DomainException,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
    Server\Process\Output,
};
use Innmind\Immutable\StreamInterface;
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
                return (string) $command === "crontab '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($this->crontab());

        $jobs = $read($server);

        $this->assertInstanceOf(StreamInterface::class, $jobs);
        $this->assertSame(Job::class, (string) $jobs->type());
        $this->assertCount(2, $jobs);
        $this->assertSame('1 2 3 4 5 echo foo', (string) $jobs->first());
        $this->assertSame('2 3 4 5 6 echo bar', (string) $jobs->last());
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
                return (string) $command === "crontab '-u' 'admin' '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($this->crontab());

        $jobs = $read($server);

        $this->assertInstanceOf(StreamInterface::class, $jobs);
        $this->assertSame(Job::class, (string) $jobs->type());
        $this->assertCount(2, $jobs);
        $this->assertSame('1 2 3 4 5 echo foo', (string) $jobs->first());
        $this->assertSame('2 3 4 5 6 echo bar', (string) $jobs->last());
    }

    public function testThrowWhenFaillingToAccessCrontab()
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
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(UnableToReadCrontab::class);

        $read($server);
    }

    public function testThrowWhenCrontabContainsInvalidJobs()
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
                return (string) $command === "crontab '-u' 'admin' '-l'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $process
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('*');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('*');

        $read($server);
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
            ->method('__toString')
            ->willReturn($crontab);

        return $output;
    }
}
