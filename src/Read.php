<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Cron\Exception\UnableToReadCrontab;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Immutable\{
    Sequence,
    Str,
};

final class Read
{
    private Command $command;

    private function __construct(Command $command)
    {
        $this->command = $command;
    }

    public static function forConnectedUser(): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('l'),
        );
    }

    public static function forUser(string $user): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user)
                ->withShortOption('l'),
        );
    }

    /**
     * @return Sequence<Job>
     */
    public function __invoke(Server $server): Sequence
    {
        $process = $server->processes()->execute($this->command);
        $process->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new UnableToReadCrontab;
        }

        return Str::of($process->output()->toString())
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->startsWith('#') && !$line->trim()->empty();
            })
            ->mapTo(
                Job::class,
                static fn(Str $line): Job => Job::of($line->toString()),
            );
    }
}
