<?php

namespace Syntax_Error_Pest;

class ForPest
{
    public function foo(): string
    {
        return 'hello';
    }

    public function hello(): string
    {
        return $this->foo();
    }
}
