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
final class FloatNegation implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a float value with its negated value. For example will replace `-33.4` with `33.4`.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = -33.4;
+ $a = 33.4;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Stmt\Return_(new Node\Scalar\DNumber(-1 * $this->getFloatValueOfNode($node), $node->getAttributes())));
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
        if (!$expr instanceof Node\Scalar\DNumber) {
            return \false;
        }
        if ($expr->value === 0.0) {
            return \false;
        }
        return \true;
    }
    /**
    @psalm-mutation-free
    */
    private function getFloatValueOfNode(Node $node) : float
    {
        $expression = $node->expr;
        if ($expression instanceof Node\Expr\UnaryMinus) {
            $innerExpression = $expression->expr;
            return -$innerExpression->value;
        }
        return $expression->value;
    }
}
