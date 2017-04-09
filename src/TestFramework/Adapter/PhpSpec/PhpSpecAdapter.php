<?php

declare(strict_types=1);

namespace Infection\TestFramework\Adapter\PhpSpec;

use Infection\TestFramework\Adapter\TestFrameworkAdapter;

class PhpSpecAdapter implements TestFrameworkAdapter
{
    const NAME = 'phpspec';

    public function getExecutableCommandLine(): string
    {
        return 'todo';
    }
}