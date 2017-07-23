<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\CommandLine;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;

class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    public function build(string $configPath): string
    {
        $options = ['run'];

        $options[] = sprintf('--config=%s', $configPath);
        $options[] = '--no-ansi';
        $options[] = '--format=tap';
        $options[] = '--stop-on-failure';

        return implode(' ', $options);
    }
}
