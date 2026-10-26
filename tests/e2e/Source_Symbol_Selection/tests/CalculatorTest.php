<?php

declare(strict_types=1);

namespace SourceSymbolSelection\Test;

use Differ;
use Differ\ColorizedDiffer;
use PHPUnit\Framework\TestCase;
use SourceSymbolSelection\Differ\Differ as MatchingDiffer;
use SourceSymbolSelection\Differ\Helper;
use SourceSymbolSelection\Other\Differ as OtherDiffer;
use SourceSymbolSelection\Calculator;

final class CalculatorTest extends TestCase
{
    public function test_differ_declarations(): void
    {
        $this->assertSame(
            2,
            (new ColorizedDiffer())->add(1),
        );
        $this->assertSame(
            2,
            (new MatchingDiffer())->add(1),
            'The matching class in a matching path must be covered.',
        );
        $this->assertSame(
            2,
            (new Helper())->add(1),
            'The other class in a matching path must be covered.',
        );
        $this->assertSame(
            2,
            (new OtherDiffer())->add(1),
            'The matching short class in another path must be covered.',
        );
        $this->assertSame(
            2,
            (new Differ())->add(1),
            'The global class in another path must be covered.',
        );
    }

    public function test_arithmetic(): void
    {
        $calculator = new Calculator();

        $this->assertSame(
            3,
            $calculator->add(
                2,
                1,
            ),
            'The selected method must add its operands.',
        );
        $this->assertSame(
            6,
            $calculator->addThree(
                1,
                2,
                3,
            ),
            'The unselected method must also be covered and detect both addition mutations.',
        );
    }
}
