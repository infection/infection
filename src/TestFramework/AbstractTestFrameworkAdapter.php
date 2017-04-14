<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;
use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\ConfigBuilder;

abstract class AbstractTestFrameworkAdapter
{
    /**
     * @var AbstractExecutableFinder
     */
    private $executableFinder;
    /**
     * @var CommandLineArgumentsAndOptionsBuilder
     */
    private $argumentsAndOptionsBuilder;

    /**
     * @var ConfigBuilder
     */
    private $configBuilder;

    public function __construct(AbstractExecutableFinder $executableFinder, ConfigBuilder $configBuilder, CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder)
    {
        $this->executableFinder = $executableFinder;
        $this->configBuilder = $configBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
    }

    /**
     * Returns path to the test framework's executable
     * Example:
     *     bin/phpspec [arguments] [--options]
     *     bin/phpunit
     *     vendor/phpunit/phpunit/phpunit
     *
     * @param string $configPath
     * @return string
     */
    public function getExecutableCommandLine($configPath) : string
    {
        return sprintf(
            '%s %s',
            $this->executableFinder->find(),
            $this->argumentsAndOptionsBuilder->build($configPath)
        );
    }

    public function buildConfigFile(Mutant $mutant = null) : string
    {
        return $this->configBuilder->build($mutant)->getPath();
    }
}