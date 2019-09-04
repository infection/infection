<?php

declare(strict_types=1);


namespace Infection\TestFramework\Codeception\CommandLine;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;

/**
 * @internal
 */
final class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    public function build(string $configPath, string $extraOptions): array
    {
        return array_filter([
            'run',
            '--no-colors',
            '--fail-fast',
            '--config',
            $configPath,
            // todo make the same fix for space in extra options and add test
            $extraOptions
        ]);
    }
}
