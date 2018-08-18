<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

use Infection\Console\ConsoleOutput;
use Infection\Console\Exception\InfectionException;

/**
 * @internal
 */
final class CoverageDoesNotExistException extends InfectionException
{
    public static function with(string $coverageIndexFilePath, string $testFrameworkKey, string $tempDir): self
    {
        return new self(
            sprintf(
                'Code Coverage does not exist. File %s is not found. Check %s version Infection was run with and generated config files inside %s. Make sure to either: %s%s',
                $coverageIndexFilePath,
                $testFrameworkKey,
                $tempDir,
                PHP_EOL,
                ConsoleOutput::INFECTION_USAGE_SUGGESTION
            )
        );
    }

    public static function forJunit(string $filePath): self
    {
        return new self(sprintf('Coverage report `junit` is not found in %s', $filePath));
    }

    public static function forFileAtPath(string $fileName, string $path): self
    {
        return new self(sprintf('Source file %s was not found at %s', $fileName, $path));
    }
}
