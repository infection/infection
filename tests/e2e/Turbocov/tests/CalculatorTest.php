<?php

namespace Infection\E2ETests\Turbocov\Tests;

use Infection\E2ETests\Turbocov\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function test_add(): void
    {
        $this->assertSame(5, (new Calculator())->add(2, 3));
    }
}
