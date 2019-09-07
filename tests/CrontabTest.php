<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
    Exception\ScriptFailed,
};
use PHPUnit\Framework\TestCase;

class CrontabTest extends TestCase
{
    public function testInstallEmptyCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser();
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return (string) $command === "crontab '-r'";
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

        $this->assertNull($crontab($server));
    }

    public function testInstallEmptyCrontabForSpecificUser()
    {
        $crontab = Crontab::forUser('admin');
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return (string) $command === "crontab '-u' 'admin' '-r'";
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

        $this->assertNull($crontab($server));
    }

    public function testInstallCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser(
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar')
        );
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return (string) $command === "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | 'crontab'";
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

        $this->assertNull($crontab($server));
    }

    public function testInstallCrontabForSpecificUser()
    {
        $crontab = Crontab::forUser(
            'admin',
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar')
        );
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command) {
                return (string) $command === "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | 'crontab' '-u' 'admin'";
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

        $this->assertNull($crontab($server));
    }

    public function testExceptionThrowWhenInstallFailed()
    {
        $crontab = Crontab::forUser(
            'admin',
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar')
        );
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
            ->expects($this->any())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(ScriptFailed::class);

        $crontab($server);
    }
}
