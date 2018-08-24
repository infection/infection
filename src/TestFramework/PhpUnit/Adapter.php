<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\MemoryUsageAware;

/**
 * @internal
 */
final class Adapter extends AbstractTestFrameworkAdapter implements MemoryUsageAware
{
    public const JUNIT_FILE_NAME = 'phpunit.junit.xml';

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output)) {
            return false;
        }

        if (preg_match('/errors!/i', $output)) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output);

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output);

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output);

        return $isOk || $isOkWithInfo || $isWarning;
    }

    public function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))MB/', $output, $match)) {
            return (float) $match[1];
        }

        return -1;
    }

    public function getName(): string
    {
        return 'PHPUnit';
    }
}
