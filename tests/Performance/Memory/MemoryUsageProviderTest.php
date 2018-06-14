<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Memory;

use Infection\Performance\Memory\MemoryUsageProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class MemoryUsageProviderTest extends TestCase
{
    public function test_it_returns_memory_usage()
    {
        $memoryUsageProvider = new MemoryUsageProvider();

        $usedMemory = $memoryUsageProvider->getPeakUsage();

        $this->assertInternalType('int', $usedMemory);
        $this->assertGreaterThan(0, $usedMemory);
    }
}
