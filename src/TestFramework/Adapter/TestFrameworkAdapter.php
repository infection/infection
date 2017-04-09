<?php

declare(strict_types=1);

namespace Infection\TestFramework\Adapter;

interface TestFrameworkAdapter
{
    /**
     * Returns path to the test framework's executable
     * Example:
     *     bin/phpspec
     *     bin/phpunit
     *     vendor/phpunit/phpunit/phpunit
     *
     * @return string
     */
    public function getExecutableCommandLine() : string;
}