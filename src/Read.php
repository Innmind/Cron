<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Immutable\{
    Sequence,
    Str,
    Maybe,
};

final class Read
{
    private Command $command;

    private function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @return Maybe<Sequence<Job>> Returns nothing when unable to read the crontab
     */
    public function __invoke(Server $server): Maybe
    {
        $process = $server->processes()->execute($this->command);
        $success = $process->wait()->match(
            static fn() => true,
            static fn() => false,
        );

        if (!$success) {
            /** @var Maybe<Sequence<Job>> */
            return Maybe::nothing();
        }

        $jobs = Str::of($process->output()->toString())
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->startsWith('#') && !$line->trim()->empty();
            })
            ->map(
                static fn(Str $line) => Job::maybe($line->toString()),
            );

        /**
         * @psalm-suppress NamedArgumentNotAllowed
         * @var Maybe<Sequence<Job>>
         */
        return $jobs->match(
            static fn($first, $jobs) => Maybe::all($first, ...$jobs->toList())->map(
                static fn(Job ...$jobs) => Sequence::of(...$jobs),
            ),
            static fn() => Maybe::just(Sequence::of()),
        );
    }

    public static function forConnectedUser(): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('l'),
        );
    }

    /**
     * @param non-empty-string $user
     */
    public static function forUser(string $user): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user)
                ->withShortOption('l'),
        );
    }
}
