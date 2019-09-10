<?php declare(strict_types=1);

namespace MultilineStatement;

use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /** @var Calculator */
    private $calculator;

    public function setUp(): void
    {
        parent::setUp();
        $this->calculator = new Calculator();
    }

    public function testCalculateInMultipleLines()
    {
        $result = $this->calculator->calculateInMultipleLines(110);

        $this->assertSame(110., $result);
    }

    public function testCalculateInMultipleLinesCustom()
    {
        $result = $this->calculator->calculateInMultipleLines(110, 200, 300);

        $this->assertSame(165., $result);
    }

    public function testCalculateInSingleLine()
    {
        $result = $this->calculator->calculateInSingleLine(110);

        $this->assertSame(110., $result);
    }

    public function testCalculateInMultipleLinesFloat()
    {
        $result = $this->calculator->calculateInMultipleLines(10.5);

        $this->assertSame(10.5, $result);
    }

    public function testCalculateInSingleLineFloat()
    {
        $result = $this->calculator->calculateInSingleLine(10.5);

        $this->assertSame(10.5, $result);
    }

    public function testCalculationsAreTheSame()
    {
        $value = 100;

        $multipleLineResult = $this->calculator->calculateInMultipleLines($value);
        $singleLineResult = $this->calculator->calculateInSingleLine($value);

        $this->assertSame($multipleLineResult, $singleLineResult);
    }
}
