<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Script,
    ScriptFailed,
};
use Innmind\Immutable\{
    Sequence,
    Str,
    SideEffect,
    Either,
};

final class Crontab
{
    private Command $command;

    /**
     * @no-named-arguments
     * @psalm-mutation-free
     */
    private function __construct(Command $command, Job ...$jobs)
    {
        $jobs = Sequence::of(...$jobs)->map(
            static fn(Job $job): string => $job->toString(),
        );

        if ($jobs->empty()) {
            $this->command = $command->withShortOption('r');
        } else {
            $this->command = Command::foreground('echo')
                ->withArgument(Str::of("\n")->join($jobs)->toString())
                ->pipe($command);
        }
    }

    /**
     * @return Either<ScriptFailed, SideEffect>
     */
    public function __invoke(Server $server): Either
    {
        $installOn = new Script($this->command);

        return $installOn($server);
    }

    /**
     * @no-named-arguments
     * @psalm-pure
     */
    public static function forConnectedUser(Job ...$jobs): self
    {
        return new self(Command::foreground('crontab'), ...$jobs);
    }

    /**
     * @no-named-arguments
     * @psalm-pure
     *
     * @param non-empty-string $user
     */
    public static function forUser(string $user, Job ...$jobs): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user),
            ...$jobs,
        );
    }
}
