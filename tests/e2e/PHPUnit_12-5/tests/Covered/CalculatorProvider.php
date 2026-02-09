<?php

namespace Infection\E2ETests\PHPUnit_12_5\Tests\Covered;

final class CalculatorProvider
{
    public static function provideAdditions(): iterable
    {
        yield [2, 3, 5];
        yield [-5, 5, 0];
        yield [-5, -5, -10];
    }
}