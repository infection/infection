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
use Infection\Tests\Mutator\AbstractMutator;
use PhpParser\Node\Scalar\LNumber;

class MinusTest extends AbstractMutator
{
    public function test_it_should_mutate_minus_expression()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Minus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    public function test_it_mutates()
    {
        $input = <<<'CODE'
<?php 

$a = 1 - 1;
CODE;
        $expectedMutatedCode = <<<'CODE'
<?php

$a = 1 + 1;
CODE;

        $mutatedCode = $this->mutate($input);

        $this->assertSame($expectedMutatedCode, $mutatedCode);
    }

    protected function getMutator(): Mutator
    {
        return new Minus();
    }
}
