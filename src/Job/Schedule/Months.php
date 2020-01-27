<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

final class Months
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function of(string $value): self
    {
        $validate = new Range('(0?[1-9]|1[0-2])');

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
