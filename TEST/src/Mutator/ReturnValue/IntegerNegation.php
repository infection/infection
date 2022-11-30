<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ReturnValue;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class IntegerNegation implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an integer value with its negated value. For example will replace `-5` with `5`.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = -5;
+ $a = 5;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Return_(new Node\Scalar\LNumber(-1 * $this->getIntegerValueOfNode($node), $node->getAttributes())));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Stmt\Return_) {
            return \false;
        }
        $expr = $node->expr;
        if ($expr instanceof Node\Expr\UnaryMinus) {
            $expr = $expr->expr;
        }
        if (!$expr instanceof Node\Scalar\LNumber) {
            return \false;
        }
        if ($expr->value === 0) {
            return \false;
        }
        return \true;
    }
    /**
    @psalm-mutation-free
    */
    private function getIntegerValueOfNode(Node $node) : int
    {
        $expression = $node->expr;
        if ($expression instanceof Node\Expr\UnaryMinus) {
            $innerExpression = $expression->expr;
            return -$innerExpression->value;
        }
        return $expression->value;
    }
}
