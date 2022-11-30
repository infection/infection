<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ConditionalBoundary;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class LessThan implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a less-than operator (`<`) with the less-than-or-equal-to operator (`<=`).
TXT
, MutatorCategory::SEMANTIC_ADDITION, <<<'TXT'
This mutator shifts the compared values highlighting an untested boundary.
TXT
, <<<'DIFF'
- $a = $b < $c;
+ $a = $b <= $c;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\BinaryOp\SmallerOrEqual($node->left, $node->right, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\Smaller;
    }
}
