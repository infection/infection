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
    public function test_it_should_mutate_plus_expression()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Plus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    public function test_it_mutates()
    {
        $input = <<<'CODE'
<?php 

$a = 1 + 1;
CODE;
        $expectedMutatedCode = <<<'CODE'
<?php

$a = 1 - 1;
CODE;

        $mutatedCode = $this->mutate($input);

        $this->assertSame($expectedMutatedCode, $mutatedCode);
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
