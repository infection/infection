<?php

declare(strict_types=1);

namespace Infection\TestFramework;


use Infection\TestFramework\PhpSpec\Adapter\PhpSpecAdapter;
use Infection\TestFramework\PhpSpec\Finder\PhpSpecExecutableFinder;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Finder\PhpUnitExecutableFinder;

class Factory
{
    public function create($adapterName) : AbstractTestFrameworkAdapter
    {
        if ($adapterName === PhpUnitAdapter::NAME) {
            return new PhpUnitAdapter(new PhpUnitExecutableFinder());
        }

        if ($adapterName === PhpSpecAdapter::NAME) {
            return new PhpSpecAdapter(new PhpSpecExecutableFinder());
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', [PhpUnitAdapter::NAME, PhpSpecAdapter::NAME])
            )
        );
    }
}