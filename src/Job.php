<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Cron\{
    Job\Schedule,
    Exception\DomainException,
};
use Innmind\Server\Control\Server\Command;
use Innmind\Immutable\Str;
use function Innmind\Immutable\join;

final class Job
{
    private Schedule $schedule;
    private Command $command;

    public function __construct(Schedule $schedule, Command $command)
    {
        $this->schedule = $schedule;
        $this->command = $command;
    }

    public static function of(string $value): self
    {
        $parts = Str::of($value)
            ->split(' ')
            ->mapTo(
                'string',
                static fn(Str $part): string => $part->toString(),
            );

        if ($parts->size() < 6) {
            throw new DomainException($value);
        }

        try {
            return new self(
                Schedule::of(join(' ', $parts->take(5))->toString()),
                Command::foreground(join(' ', $parts->drop(5))->toString()),
            );
        } catch (DomainException $e) {
            throw new DomainException($value);
        }
    }

    public function toString(): string
    {
        $command = $this->command->environment()->reduce(
            '',
            static function(string $command, string $key, string $value): string {
                return "$command$key=$value ";
            }
        );

        if ($this->command->hasWorkingDirectory()) {
            $command .= 'cd '.$this->command->workingDirectory()->toString().' && ';
        }

        $command .= $this->command->toString();

        return "{$this->schedule->toString()} $command";
    }
}
