<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\Server\Control\{
    Server,
    Server\Process\Builder,
};
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CrontabTest extends TestCase
{
    public function testInstallEmptyCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser();
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-r'",
                    $command->toString(),
                );

                return Attempt::result(Builder::foreground(2)->build());
            },
        );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testInstallEmptyCrontabForSpecificUser()
    {
        $crontab = Crontab::forUser('admin');
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-u' 'admin' '-r'",
                    $command->toString(),
                );

                return Attempt::result(Builder::foreground(2)->build());
            },
        );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testInstallCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser(
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar'),
        );
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | crontab",
                    $command->toString(),
                );

                return Attempt::result(Builder::foreground(2)->build());
            },
        );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testInstallCrontabForSpecificUser()
    {
        $crontab = Crontab::forUser(
            'admin',
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar'),
        );
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | crontab '-u' 'admin'",
                    $command->toString(),
                );

                return Attempt::result(Builder::foreground(2)->build());
            },
        );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testReturnErrorWhenInstallFailed()
    {
        $crontab = Crontab::forUser(
            'admin',
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar'),
        );
        $server = Server::via(
            static fn() => Attempt::result(
                Builder::foreground(2)
                    ->failed()
                    ->build(),
            ),
        );

        $error = $crontab($server)->match(
            static fn() => null,
            static fn($error) => $error,
        );

        $this->assertInstanceOf(\Exception::class, $error);
    }
}
