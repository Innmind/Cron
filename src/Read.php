<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Cron\Exception\UnableToReadCrontab;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Str,
};

final class Read
{
    private $command;

    private function __construct(Command $command)
    {
        $this->command = $command;
    }

    public static function forConnectedUser(): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('l')
        );
    }

    public static function forUser(string $user): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user)
                ->withShortOption('l')
        );
    }

    /**
     * @return StreamInterface<Job>
     */
    public function __invoke(Server $server): StreamInterface
    {
        $process = $server
            ->processes()
            ->execute($this->command)
            ->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new UnableToReadCrontab;
        }

        return Str::of((string) $process->output())
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->startsWith('#') && !$line->trim()->empty();
            })
            ->reduce(
                Stream::of(Job::class),
                static function(StreamInterface $jobs, Str $line): StreamInterface {
                    return $jobs->add(Job::of((string) $line));
                }
            );
    }
}
