<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Scalar\LNumber;

class MinusTest extends AbstractMutatorTestCase
{
    public function test_it_should_mutate_minus_expression()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Minus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It mutates normal minus' => [
                <<<'CODE'
<?php

$a = 1 - 1;
CODE
                ,
                <<<'CODE'
<?php

$a = 1 + 1;
CODE
                ,
            ],
            'It does not mutate minus equals' => [
                <<<'CODE'
<?php

$a = 1;
$a -= 2;
CODE
                ,
            ],
        ];
    }

    protected function getMutator(): Mutator
    {
        return new Minus();
    }
}
