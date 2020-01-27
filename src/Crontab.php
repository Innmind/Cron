<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Script,
};
use Innmind\Immutable\Sequence;

final class Crontab
{
    private Command $command;

    private function __construct(Command $command, Job ...$jobs)
    {
        $jobs = Sequence::of(...$jobs);

        if ($jobs->empty()) {
            $this->command = $command->withShortOption('r');
        } else {
            $this->command = Command::foreground('echo')
                ->withArgument((string) $jobs->join("\n"))
                ->pipe($command);
            }
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
            ...$jobs
        );
    }

    public function __invoke(Server $server): void
    {
        $installOn = new Script($this->command);

        $installOn($server);
    }
}
