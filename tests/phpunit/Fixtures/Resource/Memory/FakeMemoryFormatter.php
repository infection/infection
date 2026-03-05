<?php
declare(strict_types=1);

namespace Infection\Tests\Fixtures\Resource\Memory;

use Infection\Resource\Memory\MemoryFormatter;
use Infection\Telemetry\Metric\Memory\MemoryUsage;

final class FakeMemoryFormatter extends MemoryFormatter
{
    public function __construct(private readonly float $bytes)
    {
    }

    public function toHumanReadableString(float|MemoryUsage $bytes): string
    {
        return parent::toHumanReadableString($this->bytes);
    }
}
