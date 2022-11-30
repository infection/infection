<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Number;

use function in_array;
use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use const PHP_INT_MAX;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@extends
*/
final class DecrementInteger extends AbstractNumberMutator
{
    use GetMutatorName;
    private const COUNT_NAMES = ['count', 'grapheme_strlen', 'iconv_strlen', 'mb_strlen', 'sizeof', 'strlen'];
    public static function getDefinition() : ?Definition
    {
        return new Definition('Decrements an integer value with 1.', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = 20;
+ $a = 19;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $parentNode = ParentConnector::getParent($node);
        $value = $node->value - 1;
        if ($parentNode instanceof Node\Expr\UnaryMinus) {
            $value = $node->value + 1;
        }
        (yield new Node\Scalar\LNumber($value));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Scalar\LNumber) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($node);
        if ($node->value === PHP_INT_MAX && $parentNode instanceof Node\Expr\UnaryMinus) {
            return \false;
        }
        if ($node->value === 1 && ($this->isPartOfComparison($node) || $parentNode instanceof Node\Expr\Assign)) {
            return \false;
        }
        if ($this->isArrayZeroIndexAccess($node)) {
            return \false;
        }
        if ($this->isPartOfSizeComparison($node)) {
            return \false;
        }
        if ($this->isPregSplitLimitZeroOrMinusOneArgument($node)) {
            return \false;
        }
        return $this->isAllowedComparison($node);
    }
    private function isAllowedComparison(Node\Scalar\LNumber $node) : bool
    {
        if ($node->value !== 0) {
            return \true;
        }
        if (!$this->isPartOfComparison($node)) {
            return \true;
        }
        $parentNode = ParentConnector::getParent($node);
        if ($parentNode->left instanceof Node\Expr\FuncCall && $parentNode->left->name instanceof Node\Name && in_array($parentNode->left->name->toLowerString(), self::COUNT_NAMES, \true)) {
            return \false;
        }
        if ($parentNode->right instanceof Node\Expr\FuncCall && $parentNode->right->name instanceof Node\Name && in_array($parentNode->right->name->toLowerString(), self::COUNT_NAMES, \true)) {
            return \false;
        }
        return \true;
    }
    private function isArrayZeroIndexAccess(Node\Scalar\LNumber $node) : bool
    {
        if ($node->value !== 0) {
            return \false;
        }
        if (ParentConnector::getParent($node) instanceof Node\Expr\ArrayDimFetch) {
            return \true;
        }
        return \false;
    }
    private function isPregSplitLimitZeroOrMinusOneArgument(Node\Scalar\LNumber $node) : bool
    {
        if ($node->value !== 0) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($node);
        if (!$parentNode instanceof Node\Arg) {
            if (!$parentNode instanceof Node\Expr\UnaryMinus) {
                return \false;
            }
            $parentNode = ParentConnector::getParent($node);
            if (!$parentNode instanceof Node\Arg) {
                return \false;
            }
        }
        $parentNode = ParentConnector::getParent($parentNode);
        return $parentNode instanceof Node\Expr\FuncCall && $parentNode->name instanceof Node\Name && $parentNode->name->toLowerString() === 'preg_split';
    }
}
