<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Sort;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use function is_numeric;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@implements
*/
final class Spaceship implements Mutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition(<<<'TXT'
Swaps the spaceship operator (`<=>`) operands, e.g. replaces `$a <=> $b` with `$b <=> $a`.
TXT
, MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = $b <=> $c;
+ $a = $c <=> $b;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        (yield new Node\Expr\BinaryOp\Spaceship($node->right, $node->left));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Spaceship) {
            return \false;
        }
        if ($this->isCompareWithZero($node)) {
            return \false;
        }
        return \true;
    }
    private function isCompareWithZero(Node\Expr\BinaryOp\Spaceship $node) : bool
    {
        $parentAttribute = $node->getAttribute('parent');
        if ($parentAttribute instanceof Node\Expr\BinaryOp\Identical) {
            return $this->isIntegerScalarEqualToZero($parentAttribute);
        }
        if ($parentAttribute instanceof Node\Expr\BinaryOp\Equal) {
            return $this->isEqualToZero($parentAttribute);
        }
        return \false;
    }
    private function isIntegerScalarEqualToZero(Node\Expr\BinaryOp $node) : bool
    {
        if (!$node instanceof Node\Expr\BinaryOp\Identical && !$node instanceof Node\Expr\BinaryOp\Equal) {
            return \false;
        }
        if ($node->right instanceof Node\Scalar\LNumber && $node->right->value === 0) {
            return \true;
        }
        if ($node->left instanceof Node\Scalar\LNumber && $node->left->value === 0) {
            return \true;
        }
        return \false;
    }
    private function isEqualToZero(Node\Expr\BinaryOp\Equal $node) : bool
    {
        if ($this->isIntegerScalarEqualToZero($node)) {
            return \true;
        }
        if ($node->right instanceof Node\Scalar\DNumber && $node->right->value === 0.0) {
            return \true;
        }
        if ($node->left instanceof Node\Scalar\DNumber && $node->left->value === 0.0) {
            return \true;
        }
        if ($node->right instanceof Node\Scalar\String_ && is_numeric($node->right->value) && ($node->right->value === '0' || $node->right->value === '0.0')) {
            return \true;
        }
        if ($node->left instanceof Node\Scalar\String_ && is_numeric($node->left->value) && ($node->left->value === '0' || $node->left->value === '0.0')) {
            return \true;
        }
        return \false;
    }
}
