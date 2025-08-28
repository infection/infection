<?php declare(strict_types=1);
namespace Infection\Telemetry\Metric\Memory;

final readonly class MemoryUsage
{
    private function __construct(public int $bytes)
    {
    }

    public static function fromBytes(int $bytes): self
    {
        return new self($bytes);
    }

    public function diff(self $other): self
    {
        return self::fromBytes($this->bytes - $other->bytes);
    }
}
