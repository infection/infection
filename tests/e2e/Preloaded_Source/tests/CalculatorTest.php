<?php

namespace Infection\E2ETests\PreloadedSource\Tests;

use Infection\E2ETests\PreloadedSource\Calculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
class CalculatorTest extends TestCase
{
    public function test_it_adds_two_numbers(): void
    {
        $this->assertSame(3, (new Calculator())->add(1, 2));
    }
}
