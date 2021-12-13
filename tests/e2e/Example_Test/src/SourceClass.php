<?php

namespace ExampleTest;

class SourceClass
{
    private const TWO = 2;

    public function hello(): string
    {
        $a = 1 + self::TWO;

        return 'hello';
    }
}
