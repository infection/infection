<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Performance\Memory;

/**
 * @internal
 */
final class MemoryUsageProvider
{
    public function getPeakUsage(): int
    {
        return memory_get_peak_usage(true);
    }
}
