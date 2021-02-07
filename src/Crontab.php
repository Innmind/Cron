<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Script,
};
use Innmind\Immutable\Sequence;
use function Innmind\Immutable\join;

final class Crontab
{
    private Command $command;

    private function __construct(Command $command, Job ...$jobs)
    {
        $jobs = Sequence::of(Job::class, ...$jobs)->mapTo(
            'string',
            static fn(Job $job): string => $job->toString(),
        );

        if ($jobs->empty()) {
            $this->command = $command->withShortOption('r');
        } else {
            $this->command = Command::foreground('echo')
                ->withArgument(join("\n", $jobs)->toString())
                ->pipe($command);
        }
    }

    public function __invoke(Server $server): void
    {
        $installOn = new Script($this->command);

        $installOn($server);
    }

    public static function forConnectedUser(Job ...$jobs): self
    {
        return new self(Command::foreground('crontab'), ...$jobs);
    }

    public static function forUser(string $user, Job ...$jobs): self
    {
        return new self(
            Command::foreground('crontab')
                ->withShortOption('u', $user),
            ...$jobs,
        );
    }
}
