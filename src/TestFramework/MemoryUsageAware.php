<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework;

/**
 * @internal
 */
interface MemoryUsageAware
{
    /**
     * Reports memory used by a test suite, or -1 if it cant detect the memory usage.
     *
     * @param string $output
     *
     * @return float
     */
    public function getMemoryUsed(string $output): float;
}
