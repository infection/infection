<?php

namespace PestTestFramework;

class ForPest
{
    public function hello(): string
    {
        return 'hello';
    }

    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
