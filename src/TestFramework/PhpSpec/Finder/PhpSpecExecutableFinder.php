<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Finder;

use Infection\Finder\AbstractExecutableFinder;

class PhpSpecExecutableFinder extends AbstractExecutableFinder
{
    public function find()
    {
        return 'todo';
    }
}