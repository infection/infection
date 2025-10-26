<?php

namespace Custom_Mutator;

class SourceClass
{
    public function hello(): string
    {
        $a = 1;
        $b = $a + 2;

        return 'hello';
    }
}
