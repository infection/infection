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
final class Increment implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a pre- or post-increment operator (`++`) with the analogue pre- or post-decrement operator
(`--`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a++;
+ $a--;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        if ($node instanceof Node\Expr\PreInc) {
            (yield new Node\Expr\PreDec($node->var, $node->getAttributes()));
            return;
        }
        if ($node instanceof Node\Expr\PostInc) {
            (yield new Node\Expr\PostDec($node->var, $node->getAttributes()));
            return;
        }
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\PreInc || $node instanceof Node\Expr\PostInc;
    }
}
