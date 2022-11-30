<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Number;

use _HumbugBox9658796bb9f0\Infection\Mutator\Definition;
use _HumbugBox9658796bb9f0\Infection\Mutator\GetMutatorName;
use _HumbugBox9658796bb9f0\Infection\Mutator\MutatorCategory;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use const PHP_INT_MAX;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@extends
*/
final class IncrementInteger extends AbstractNumberMutator
{
    use GetMutatorName;
    public static function getDefinition() : ?Definition
    {
        return new Definition('Increments an integer value with 1.', MutatorCategory::ORTHOGONAL_REPLACEMENT, null, <<<'DIFF'
- $a = 20;
+ $a = 21;
DIFF
);
    }
    /**
    @psalm-mutation-free
    */
    public function mutate(Node $node) : iterable
    {
        $parentNode = ParentConnector::getParent($node);
        $value = $node->value + 1;
        if ($parentNode instanceof Node\Expr\UnaryMinus) {
            $value = $node->value - 1;
        }
        (yield new Node\Scalar\LNumber($value));
    }
    public function canMutate(Node $node) : bool
    {
        if (!$node instanceof Node\Scalar\LNumber) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($node);
        if ($node->value === PHP_INT_MAX && !$parentNode instanceof Node\Expr\UnaryMinus) {
            return \false;
        }
        if ($node->value === 0 && ($this->isPartOfComparison($node) || $parentNode instanceof Node\Expr\Assign)) {
            return \false;
        }
        if ($this->isPartOfSizeComparison($node)) {
            return \false;
        }
        if ($this->isPregSplitLimitZeroOrMinusOneArgument($node)) {
            return \false;
        }
        return \true;
    }
    private function isPregSplitLimitZeroOrMinusOneArgument(Node\Scalar\LNumber $node) : bool
    {
        if ($node->value !== 1) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($node);
        if (!$parentNode instanceof Node\Expr\UnaryMinus) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($parentNode);
        if (!$parentNode instanceof Node\Arg) {
            return \false;
        }
        $parentNode = ParentConnector::getParent($parentNode);
        return $parentNode instanceof Node\Expr\FuncCall && $parentNode->name instanceof Node\Name && $parentNode->name->toLowerString() === 'preg_split';
    }
}
