<?php
declare(strict_types = 1);

namespace Innmind\Cron;

use Innmind\Cron\Job\Schedule;
use Innmind\Server\Control\Server\Command;
use Innmind\Immutable\{
    Str,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Job
{
    private Schedule $schedule;
    private Command $command;

    public function __construct(Schedule $schedule, Command $command)
    {
        $this->schedule = $schedule;
        $this->command = $command;
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $value
     *
     * @throws \DomainException
     */
    public static function of(string $value): self
    {
        return self::maybe($value)->match(
            static fn($self) => $self,
            static fn() => throw new \DomainException($value),
        );
    }

    /**
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(string $value): Maybe
    {
        $parts = Str::of($value)
            ->split(' ')
            ->map(static fn(Str $part): string => $part->toString());

        $command = Str::of(' ')->join($parts->drop(5))->toString();

        if ($command === '') {
            /** @var Maybe<self> */
            return Maybe::nothing();
        }

        return Schedule::maybe(Str::of(' ')->join($parts->take(5))->toString())
            ->map(static fn($schedule) => new self(
                $schedule,
                Command::foreground($command),
            ));
    }

    public function toString(): string
    {
        $command = $this->command->environment()->reduce(
            '',
            static function(string $command, string $key, string $value): string {
                return "$command$key=$value ";
            },
        );

        $command .= $this->command->workingDirectory()->match(
            static fn($workingDirectory) => 'cd '.$workingDirectory->toString().' && ',
            static fn() => '',
        );

        $command .= $this->command->toString();

        return "{$this->schedule->toString()} $command";
    }
}
