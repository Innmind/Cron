<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

final class DaysOfMonth
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function of(string $value): self
    {
        $validate = new Range('([0-2]?[0-9]|30|31)');

        $validate($value);

        return new self($value);
    }

    public static function each(): self
    {
        return new self('*');
    }

    public function toString(): string
    {
        return $this->value;
    }
}
