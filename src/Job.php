<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Cron\{
    Job\Schedule,
    Exception\DomainException,
};
use Innmind\Server\Control\Server\Command;
use Innmind\Immutable\Str;

final class Job
{
    private $schedule;
    private $command;

    public function __construct(Schedule $schedule, Command $command)
    {
        $this->schedule = $schedule;
        $this->command = $command;
    }

    public static function of(string $value): self
    {
        $parts = Str::of($value)->split(' ');

        if ($parts->size() < 6) {
            throw new DomainException($value);
        }

        try {
            return new self(
                Schedule::of((string) $parts->take(5)->join(' ')),
                Command::foreground((string) $parts->drop(5)->join(' '))
            );
        } catch (DomainException $e) {
            throw new DomainException($value);
        }
    }

    public function __toString(): string
    {
        $command = $this->command->environment()->reduce(
            '',
            static function(string $command, string $key, string $value): string {
                return "$command$key=$value ";
            }
        );

        if ($this->command->hasWorkingDirectory()) {
            $command .= 'cd '.$this->command->workingDirectory().' && ';
        }

        $command .= $this->command;

        return "$this->schedule $command";
    }
}
