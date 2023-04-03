<?php
declare(strict_types=1);

namespace Infection\Tests\Fixtures\Resource\Memory;

use Infection\Resource\Memory\MemoryFormatter;

final class FakeMemoryFormatter extends MemoryFormatter
{
    public function __construct(private float $bytes)
    {
    }

    public function toHumanReadableString(float $bytes): string
    {
        return parent::toHumanReadableString($this->bytes);
    }
}
