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
final class MemoryFormatter
{
    private const BYTES_IN_MEGABYTE = 1024 * 1024;

    public function toHumanReadableString(float $bytes): string
    {
        return sprintf('%.2fMB', $bytes / self::BYTES_IN_MEGABYTE);
    }
}
