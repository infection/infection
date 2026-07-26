<?php

namespace Infection\E2ETests\PHPUnit_12_5\Tests\Covered;

use Infection\E2ETests\PHPUnit_12_5\Covered\Calculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calculator::class)]
class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    #[DataProviderExternal(CalculatorProvider::class, 'provideAdditions')]
    public function test_add(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $this->calculator->add($a, $b));
    }

    #[DataProvider('provideSubstractions')]
    public function test_subtract(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $this->calculator->subtract($a, $b));
    }

    public static function provideSubstractions(): iterable
    {
        yield [5, 3, 2];
        yield [5, -5, 10];
        yield [5, 5, 0];
    }

    public function test_multiply(): void
    {
        $this->assertSame(15, $this->calculator->multiply(3, 5));
        $this->assertSame(-15, $this->calculator->multiply(-3, 5));
        $this->assertSame(0, $this->calculator->multiply(0, 5));
    }

    public function test_divide(): void
    {
        $this->assertSame(2.5, $this->calculator->divide(5, 2));
        $this->assertSame(-2.5, $this->calculator->divide(-5, 2));
        $this->assertSame(1.0, $this->calculator->divide(5, 5));
    }

    public function test_divide_by_zero_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        $this->calculator->divide(5, 0);
    }

    public function test_is_positive(): void
    {
        $this->assertTrue($this->calculator->isPositive(5));
        $this->assertFalse($this->calculator->isPositive(-5));
        $this->assertTrue($this->calculator->isPositive(0));
    }

    public function test_absolute(): void
    {
        $this->assertSame(5, $this->calculator->absolute(5));
        $this->assertSame(5, $this->calculator->absolute(-5));
    }

    public function test_absolute_zero(): void
    {
        // Test edge case: 0 should not be negated (0 is not less than 0)
        $result = $this->calculator->absolute(0);
        $this->assertSame(0, $result);

        // Test boundary values - ensure the comparison is strictly less than, not less than or equal
        $this->assertSame(1, $this->calculator->absolute(1));
        $this->assertSame(1, $this->calculator->absolute(-1));

        // Verify that 0 behaves the same as positive numbers (returned as-is)
        // If the mutant changes < to <=, it would negate 0, but -0 === 0 in PHP
        // This mutant is technically equivalent and cannot be caught
        $this->assertTrue($result === 0);
    }
}
