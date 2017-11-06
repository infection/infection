<?php

namespace Infection\Tests\Files\Autoloaded;

interface SomeInterface
{
    public function foo();
}

class Child implements SomeInterface
{
    public function foo() {}
}