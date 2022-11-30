<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\ConditionalNegotiation;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class NotEqual implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Replaces a not equal operator (`!=`) with its counterpart the not equal operator (`==`).
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = $b != $c;
+ $a = $b == $c;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\BinaryOp\Equal($node->left, $node->right, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\NotEqual;
    }
}
