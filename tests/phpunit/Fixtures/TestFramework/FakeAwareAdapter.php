<?php

namespace Infection\Tests\Fixtures\TestFramework;

use ErrorException;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\Tests\UnsupportedMethod;

class FakeAwareAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware
{
    public function __construct(private readonly float $memoryLimit)
    {
    }

    public function hasJUnitReport(): bool
    {
        return false;
    }

    public function testsPass(string $output): bool
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
    }

    public function getName(): string
    {
        throw UnsupportedMethod::method(self::class, __FUNCTION__);
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
