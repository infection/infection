<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\TestFramework;

use Infection\AbstractTestFramework\MemoryUsageAware;

final class FakeAwareAdapter extends DummyTestFrameworkAdapter implements MemoryUsageAware
{
    public function __construct(private readonly float $memoryLimit)
    {
    }

    /**
     * Reports memory used by a test suite.
     *
     */
    public function getMemoryUsed(string $output): float
    {
        return $this->memoryLimit;
    }
}
