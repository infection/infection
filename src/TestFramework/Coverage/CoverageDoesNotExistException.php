<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

use Infection\Console\Exception\InfectionException;

class CoverageDoesNotExistException extends InfectionException
{
    public static function with(string $coverageIndexFilePath, string $testFrameworkKey, string $tempDir): self
    {
        return new self(
            sprintf(
                'Code Coverage does not exist. File %s is not found. Check %s version Infection was run with and generated config files inside %s.',
                $coverageIndexFilePath,
                $testFrameworkKey,
                $tempDir
            )
        );
    }
}
