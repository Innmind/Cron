<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Server\Control\{
    Server,
    Server\Command,
};
use Innmind\Immutable\{
    Sequence,
    Attempt,
    Monoid\Concat,
};

final class Read
{
    private function __construct(private Command $command)
    {
    }

    /**
     * @return Attempt<Sequence<Job>>
     */
    #[\NoDiscard]
    public function __invoke(Server $server): Attempt
    {
        return $server
            ->processes()
            ->execute($this->command)
            ->flatMap(
                static fn($process) => $process
                    ->wait()
                    ->attempt(static fn($error) => new \RuntimeException($error::class)),
            )
            ->map(
                static fn($success) => $success
                    ->output()
                    ->map(static fn($chunk) => $chunk->data())
                    ->fold(Concat::monoid)
                    ->split("\n")
                    ->filter(static fn($line) => !$line->startsWith('#') && !$line->trim()->empty())
                    ->map(static fn($line) => Job::attempt($line->toString())),
            )
            ->flatMap(self::parse(...));
    }

    #[\NoDiscard]
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
    #[\NoDiscard]
    public static function forUser(string $user): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user)
                ->withShortOption('l'),
        );
    }

    /**
     * @param Sequence<Attempt<Job>> $jobs
     *
     * @return Attempt<Sequence<Job>>
     */
    private static function parse(Sequence $jobs): Attempt
    {
        /** @var Sequence<Job> */
        $parsed = Sequence::of();

        return $jobs
            ->sink($parsed)
            ->attempt(static fn($parsed, $job) => $job->map($parsed));
    }
}
