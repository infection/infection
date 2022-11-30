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
final class Plus implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces an addition operator (`+`) with a subtraction operator (`-`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = $b + $c;
+ $a = $b - $c;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\BinaryOp\Minus($node->left, $node->right, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Plus) {
            return \false;
        }
        if ($node->left instanceof Node\Expr\Array_ || $node->right instanceof Node\Expr\Array_) {
            return \false;
        }
        return \true;
    }
}
