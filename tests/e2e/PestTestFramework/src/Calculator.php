<?php

declare(strict_types=1);


namespace PestTestFramework;


final class Calculator
{
    public function sub(int $a, int $b): int
    {
        return $a - $b;
    }

    public function mul(int $a, int $b)
    {
        return $a * $b;
    }
}
