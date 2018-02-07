<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\LNumber;

class PlusTest extends AbstractMutatorTestCase
{
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
            'It mutates normal plus' => [
                <<<'CODE'
<?php

$a = 10 + 3;
CODE
                ,
                <<<'CODE'
<?php

$a = 10 - 3;
CODE
                ,
            ],
            'It does not mutate plus equals' => [
                <<<'CODE'
<?php

$a = 1;
$a += 2;
CODE
                ,
            ],
            'It does not mutate increment' => [
                <<<'CODE'
<?php

$a = 1;
$a++;
CODE
                ,
            ],
            'It does mutate a fake increment' => [
                <<<'CODE'
<?php

$a = 1;
$a + +1;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
$a - +1;
CODE
                ,
            ],
        ];
    }

    public function test_it_should_mutate_plus_expression()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Plus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    public function test_it_should_not_mutate_plus_with_arrays()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Plus(
            new Array_([new LNumber(1)]),
            new Array_([new LNumber(1)])
        );

        $this->assertFalse($this->mutator->shouldMutate($plusExpression));
    }

    protected function getMutator(): Mutator
    {
        return new Plus();
    }
}
