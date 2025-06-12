<?php

namespace Ignore_All_Mutations;

class SourceClass
{
    /** @infection-ignore-all */
    public function hello(int $foo): string
    {
        if ($foo > 0) {
            return 'hello';
        }

        return 'bye';
    }

    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

}
