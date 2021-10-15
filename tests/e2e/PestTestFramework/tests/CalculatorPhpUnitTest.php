<?php

declare(strict_types=1);


namespace PestTestFramework\Test;


use PestTestFramework\Calculator;
use PHPUnit\Framework\TestCase;

final class CalculatorPhpUnitTest extends TestCase
{
    public function test_it_can_multiply(): void
    {
        $calculator = new Calculator();

        self::assertSame(8, $calculator->mul(2, 4));
    }
}
