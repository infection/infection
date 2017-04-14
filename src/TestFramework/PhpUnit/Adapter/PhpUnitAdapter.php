<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Adapter;

use Infection\Finder\AbstractExecutableFinder;
use Infection\TestFramework\AbstractTestFrameworkAdapter;

class PhpUnitAdapter extends AbstractTestFrameworkAdapter
{
    const NAME = 'phpunit';

    public function testsPass(string $output): bool
    {
        return (bool) preg_match('/failures!/i', $output);
    }
}