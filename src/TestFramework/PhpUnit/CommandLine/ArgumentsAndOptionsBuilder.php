<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\CommandLine;

use Infection\TestFramework\CommandLineArgumentsAndOptionsBuilder;
use Infection\TestFramework\Config\ConfigBuilder;

class ArgumentsAndOptionsBuilder implements CommandLineArgumentsAndOptionsBuilder
{
    /**
     * @var ConfigBuilder
     */
    private $configBuilder;

    public function __construct(ConfigBuilder $configBuilder)
    {
        $this->configBuilder = $configBuilder;
    }

    public function build(): string
    {
        $options = [];

        $this->configBuilder->build();

        $options[] = sprintf('--configuration %s', $this->configBuilder->getPath());

        return implode(' ', $options);
    }
}