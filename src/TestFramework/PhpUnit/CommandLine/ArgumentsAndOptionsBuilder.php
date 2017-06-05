<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\CommandLine;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Config\InitialConfigBuilder;

class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    public function build(string $configPath): string
    {
        $options = [];

        $options[] = sprintf('--configuration %s', $configPath);
        $options[] = '--stop-on-failure';

        return implode(' ', $options);
    }
}