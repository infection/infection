<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Mutator\Number;

use _HumbugBox9658796bb9f0\Infection\Mutator\Mutator;
use _HumbugBox9658796bb9f0\Infection\PhpParser\Visitor\ParentConnector;
use _HumbugBox9658796bb9f0\PhpParser\Node;
/**
@template
@implements
*/
abstract class AbstractNumberMutator implements Mutator
{
    protected function isPartOfSizeComparison(Node $node) : bool
    {
        $parent = ParentConnector::findParent($node);
        return $this->isSizeComparison($parent);
    }
    protected function isPartOfComparison(Node $node) : bool
    {
        $parent = ParentConnector::getParent($node);
        return $this->isComparison($parent);
    }
    private function isSizeComparison(?Node $node) : bool
    {
        if ($node === null) {
            return \false;
        }
        if ($node instanceof Node\Expr\UnaryMinus) {
            return $this->isSizeComparison(ParentConnector::findParent($node));
        }
        return $this->isSizeNode($node);
    }
    private function isSizeNode(Node $node) : bool
    {
        return $node instanceof Node\Expr\BinaryOp\Greater || $node instanceof Node\Expr\BinaryOp\GreaterOrEqual || $node instanceof Node\Expr\BinaryOp\Smaller || $node instanceof Node\Expr\BinaryOp\SmallerOrEqual;
    }
    private function isComparison(?Node $node) : bool
    {
        if ($node === null) {
            return \false;
        }
        if ($node instanceof Node\Expr\UnaryMinus) {
            return $this->isComparison(ParentConnector::findParent($node));
        }
        return $node instanceof Node\Expr\BinaryOp\Identical || $node instanceof Node\Expr\BinaryOp\NotIdentical || $node instanceof Node\Expr\BinaryOp\Equal || $node instanceof Node\Expr\BinaryOp\NotEqual || $this->isSizeNode($node);
    }
}
