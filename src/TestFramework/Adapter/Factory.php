<?php

declare(strict_types=1);

namespace Infection\TestFramework\Adapter;


use Infection\TestFramework\Adapter\PhpSpec\PhpSpecAdapter;
use Infection\TestFramework\Adapter\PhpUnit\PhpUnitAdapter;

class Factory
{
    public function create($adapterName)
    {
        if ($adapterName === PhpUnitAdapter::NAME) {
            return new PhpUnitAdapter();
        }

        if ($adapterName === PhpSpecAdapter::NAME) {
            return new PhpSpecAdapter();
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid name of test framework. Available names are: %s',
                implode(', ', [PhpUnitAdapter::NAME, PhpSpecAdapter::NAME])
            )
        );
    }
}