<?php

declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Finder\AbstractExecutableFinder;

abstract class AbstractTestFrameworkAdapter
{
    /**
     * @var AbstractExecutableFinder
     */
    private $executableFinder;

    public function __construct(AbstractExecutableFinder $executableFinder)
    {
        $this->executableFinder = $executableFinder;
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
        return $this->executableFinder->find();
    }
}