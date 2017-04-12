<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;
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

    public function __construct(AbstractExecutableFinder $executableFinder, CommandLineArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder)
    {
        $this->executableFinder = $executableFinder;
        $this->argumentsAndOptionsBuilder = $argumentsAndOptionsBuilder;
    }

    /**
     * Returns path to the test framework's executable
     * Example:
     *     bin/phpspec
     *     bin/phpunit
     *     vendor/phpunit/phpunit/phpunit
     *
     * @return string
     */
    public function getExecutableCommandLine() : string
    {
        return sprintf(
            '%s %s',
            $this->executableFinder->find(),
            $this->argumentsAndOptionsBuilder->build()
        );
    }
}