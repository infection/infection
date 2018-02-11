<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests;

/**
 * Normalizes path. Replaces backslashes with forward ones
 *
 * @param string $value
 *
 * @return string
 */
function normalizePath(string $value): string
{
    return \str_replace(DIRECTORY_SEPARATOR, '/', $value);
}
