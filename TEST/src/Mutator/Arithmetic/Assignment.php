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
final class Assignment implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces examples of augmented or compound (shorter way to apply an arithmetic or bitwise operation)
assignment operators, i.e. `+=`, `*=`, `.=`, etc., with a plain assignment operator `=`.
TXT
, MutatorCategory::SEMANTIC_REDUCTION, null, <<<'DIFF'
- $a += $b;
+ $a = $b;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\Assign($node->var, $node->expr, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\AssignOp && !$node instanceof Node\Expr\AssignOp\Coalesce;
    }
}
