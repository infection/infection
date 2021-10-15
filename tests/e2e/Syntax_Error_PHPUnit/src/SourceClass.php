<?php

namespace Syntax_Error_PHPUnit;

class SourceClass
{
    public function foo(): string
    {
        return 'hello';
    }

    public function bar(): string
    {
        return $this->foo();
    }
}
