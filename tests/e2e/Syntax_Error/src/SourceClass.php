<?php

namespace Syntax_Error;

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
