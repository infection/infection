<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Arithmetic;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class PlusEqual implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an addition assignment operator (`+=`) with a subtraction assignment operator (`-=`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a += $b;
+ $a -= $b;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\AssignOp\Minus($node->var, $node->expr, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\AssignOp\Plus;
    }
}
