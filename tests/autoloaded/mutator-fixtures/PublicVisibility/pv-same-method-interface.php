<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Autoloaded\PublicVisibility;

interface SomeInterface
{
    public function foo();
}

class Child implements SomeInterface
{
    public function foo()
    {
    }
}
