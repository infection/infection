<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;
use Infection\Mutant\Mutant;
use Infection\TestFramework\PhpUnit\Config\Builder\InitialConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;

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
     * @var InitialConfigBuilder
     */
    private $initialConfigBuilder;

    /**
     * @var MutationConfigBuilder
     */
    private $mutationConfigBuilder;

    public function __construct(AbstractExecutableFinder $executableFinder, InitialConfigBuilder $initialConfigBuilder, MutationConfigBuilder $mutationConfigBuilder, CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder)
    {
        $this->executableFinder = $executableFinder;
        $this->initialConfigBuilder = $initialConfigBuilder;
        $this->mutationConfigBuilder = $mutationConfigBuilder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
    }

    abstract public function testsPass(string $output) : bool;

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

    public function buildInitialConfigFile() : string
    {
        return $this->initialConfigBuilder->build();
    }

    public function buildMutationConfigFile(Mutant $mutant) : string
    {
        return $this->mutationConfigBuilder->build($mutant);
    }
}