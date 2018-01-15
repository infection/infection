<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\Codeception\CommandLine;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;

class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    public function build(string $configPath, string $extraOptions): string
    {
        $options = ['run'];

        $options[] = $extraOptions;

        return implode(' ', $options);
    }
}
