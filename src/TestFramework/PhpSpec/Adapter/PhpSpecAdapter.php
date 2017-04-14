<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Adapter;

use Infection\TestFramework\AbstractTestFrameworkAdapter;

class PhpSpecAdapter extends AbstractTestFrameworkAdapter
{
    const NAME = 'phpspec';

    public function testsPass(string $output): bool
    {
        return false;
    }
}