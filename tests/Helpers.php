<?php

declare(strict_types=1);

namespace Infection\Tests;

/**
 * Normalizes path. Replaces backslashes with forward ones
 */
function normalizePath(string $value)
{
    return str_replace(DIRECTORY_SEPARATOR, '/', $value);
}
