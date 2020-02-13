<?php

namespace Memory_Aware;

use ErrorException;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\TestFramework\AbstractTestFrameworkAdapter;

class FakeAwareAdapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware
{
    private $memoryLimit;

    public function __construct(float $memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    public function hasJUnitReport(): bool
    {
        return false;
    }

    public function testsPass(string $output): bool
    {
        throw new ErrorException('this should never be called');
    }

    public function getName(): string
    {
        throw new ErrorException('this should never be called');
    }

    /**
     * Reports memory used by a test suite.
     *
     * @param string $output
     *
     * @return float
     */
    public function getMemoryUsed(string $output): float
    {
        return $this->memoryLimit;
    }
}
