<?php

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Minus;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutator;
use PhpParser\Node\Scalar\LNumber;
use PHPUnit\Framework\TestCase;

class MinusTest extends AbstractMutator
{
    public function test_it_should_mutate_minus_expression()
    {
        $plusExpression = new \PhpParser\Node\Expr\BinaryOp\Minus(new LNumber(1), new LNumber(2));

        $this->assertTrue($this->mutator->shouldMutate($plusExpression));
    }

    protected function getMutator(): Mutator
    {
        return new Minus();
    }
}