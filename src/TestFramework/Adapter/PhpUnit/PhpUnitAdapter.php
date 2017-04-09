<?php

declare(strict_types=1);

namespace Infection\TestFramework\Adapter\PhpUnit;

use Infection\TestFramework\Adapter\TestFrameworkAdapter;

class PhpUnitAdapter implements TestFrameworkAdapter
{
    const NAME = 'phpunit';

    public function getExecutableCommandLine(): string
    {
        return 'vendor/phpunit/phpunit/phpunit';
    }
}