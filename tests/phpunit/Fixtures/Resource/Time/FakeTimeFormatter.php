<?php
declare(strict_types=1);

namespace Infection\Tests\Fixtures\Resource\Time;

use Infection\Resource\Time\TimeFormatter;

final class FakeTimeFormatter extends TimeFormatter
{
    public function __construct(private float $seconds)
    {
    }

    public function toHumanReadableString(float $seconds): string
    {
        return parent::toHumanReadableString($this->seconds);
    }
}
