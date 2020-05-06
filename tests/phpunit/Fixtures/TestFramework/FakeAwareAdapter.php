<?php

namespace Infection\Tests\Fixtures\TestFramework;

use ErrorException;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\UnsupportedTestFrameworkVersion;
use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\Tests\UnsupportedMethod;

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
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }

    public function getName(): string
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
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

    /**
     * @throws UnsupportedTestFrameworkVersion
     */
    protected function getMinimumSupportedVersion(): string
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
