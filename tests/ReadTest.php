<?php
declare(strict_types = 1);

namespace Tests\Innmind\Cron;

use Innmind\Cron\Read;
use Innmind\Server\Control\{
    Server,
    Server\Process\Builder,
};
use Innmind\Immutable\{
    Sequence,
    Attempt,
};
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ReadTest extends TestCase
{
    public function testReadCrontabForConnectedUser()
    {
        $read = Read::forConnectedUser();
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-l'",
                    $command->toString(),
                );

                return Attempt::result(
                    Builder::foreground(2)
                        ->success([
                            [
                                <<<CRONTAB
                                # First section
                                1 2 3 4 5 echo foo

                                # Second section
                                2 3 4 5 6 echo bar

                                CRONTAB,
                                'output',
                            ],
                        ])
                        ->build(),
                );
            },
        );

        $jobs = $read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        );

        $this->assertInstanceOf(Sequence::class, $jobs);
        $this->assertSame(2, $jobs->size());
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
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-u' 'admin' '-l'",
                    $command->toString(),
                );

                return Attempt::result(
                    Builder::foreground(2)
                        ->success([
                            [
                                <<<CRONTAB
                                # First section
                                1 2 3 4 5 echo foo

                                # Second section
                                2 3 4 5 6 echo bar

                                CRONTAB,
                                'output',
                            ],
                        ])
                        ->build(),
                );
            },
        );

        $jobs = $read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        );

        $this->assertInstanceOf(Sequence::class, $jobs);
        $this->assertSame(2, $jobs->size());
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
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-u' 'admin' '-l'",
                    $command->toString(),
                );

                return Attempt::result(
                    Builder::foreground(2)
                        ->success([['*', 'output']])
                        ->build(),
                );
            },
        );

        $this->assertNull($read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenProcessToReadTheCrontabFailed()
    {
        $read = Read::forUser('admin');
        $server = Server::via(
            function($command) {
                $this->assertSame(
                    "crontab '-u' 'admin' '-l'",
                    $command->toString(),
                );

                return Attempt::result(
                    Builder::foreground(2)
                        ->failed()
                        ->build(),
                );
            },
        );

        $this->assertNull($read($server)->match(
            static fn($jobs) => $jobs,
            static fn() => null,
        ));
    }
}
