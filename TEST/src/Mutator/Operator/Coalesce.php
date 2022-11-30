<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Operator;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Coalesce implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Swaps the coalesce operator (`??`) operands,
e.g. replaces `$a ?? $b` with `$b ?? $a` or `$a ?? $b ?? $c` with `$b ?? $a ?? $c` and `$a ?? $c ?? $b`.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $d = $a ?? $b ?? $c;
# Mutation 1
+ $d = $b ?? $a ?? $c;
# Mutation 2
+ $d = $a ?? $c ?? $b;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $left = $node->left;
        $right = $node->right;
        if ($right instanceof Node\Expr\BinaryOp\Coalesce) {
            $left = new Node\Expr\BinaryOp\Coalesce($node->left, $right->right, $right->getAttributes());
            $right = $right->left;
        }
        (yield new Node\Expr\BinaryOp\Coalesce($right, $left, $node->getAttributes()));
    }
    public function canMutate(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\Coalesce && !$node->left instanceof Node\Expr\ConstFetch && !$node->left instanceof Node\Expr\ClassConstFetch && !($node->right instanceof Node\Expr\ConstFetch && $node->right->name->toLowerString() === 'null');
    }
}
