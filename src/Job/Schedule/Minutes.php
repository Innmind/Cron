<?php
declare(strict_types = 1);

namespace Innmind\Cron\Job\Schedule;

final class Minutes
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function of(string $value): self
    {
        $validate = new Range('[0-5]?[0-9]');

        $validate($value);

        return new self($value);
    }

    public static function each(): self
    {
        return new self('*');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
