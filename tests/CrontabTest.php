<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\{
    Crontab,
    Job,
};
use Innmind\Server\Control\Servers\Mock;
use Innmind\Immutable\SideEffect;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class CrontabTest extends TestCase
{
    public function testInstallEmptyCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser();
        $server = Mock::new($this->assert())
            ->willExecute(
                fn($command) => $this->assertSame(
                    "crontab '-r'",
                    $command->toString(),
                ),
            );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testInstallEmptyCrontabForSpecificUser()
    {
        $crontab = Crontab::forUser('admin');
        $server = Mock::new($this->assert())
            ->willExecute(
                fn($command) => $this->assertSame(
                    "crontab '-u' 'admin' '-r'",
                    $command->toString(),
                ),
            );

        $this->assertInstanceOf(SideEffect::class, $crontab($server)->unwrap());
    }

    public function testInstallCrontabForConnectedUser()
    {
        $crontab = Crontab::forConnectedUser(
            Job::of('1 2 3 4 5 echo foo'),
            Job::of('2 3 4 5 6 echo bar'),
        );
        $server = Mock::new($this->assert())
            ->willExecute(
                fn($command) => $this->assertSame(
                    "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | 'crontab'",
                    $command->toString(),
                ),
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
        $server = Mock::new($this->assert())
            ->willExecute(
                fn($command) => $this->assertSame(
                    "echo '1 2 3 4 5 echo foo\n2 3 4 5 6 echo bar' | 'crontab' '-u' 'admin'",
                    $command->toString(),
                ),
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
        $server = Mock::new($this->assert())
            ->willExecute(
                static fn() => null,
                static fn($_, $builder) => $builder->failed(),
            );

        $error = $crontab($server)->match(
            static fn() => null,
            static fn($error) => $error,
        );

        $this->assertInstanceOf(\Exception::class, $error);
    }
}
