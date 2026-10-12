<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Tests;

use Infection\E2ETests\PHPUnitTIA\Calculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
final class CalculatorTest extends TestCase
{
    public function test_calculate(): void
    {
        $this->assertSame(3, (new Calculator())->calculate(1, 2));
    }
}
