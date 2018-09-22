<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Mutator\Number;

use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;

/**
 * @internal
 */
final class DecrementInteger extends Mutator
{
    private const COUNT_NAME = ['count', 'sizeof'];

    /**
     * Decrements an integer by 1
     *
     * @param Node $node
     *
     * @return Node\Scalar\LNumber
     */
    public function mutate(Node $node)
    {
        return new Node\Scalar\LNumber($node->value - 1);
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Scalar\LNumber || $node->value === 1) {
            return false;
        }

        return $this->isAllowedComparison($node);
    }

    private function isAllowedComparison(Node\Scalar\LNumber $node): bool
    {
        if ($node->value !== 0) {
            return true;
        }

        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if (!$this->isComparison($parentNode)) {
            return true;
        }

        if ($parentNode->left instanceof Node\Expr\FuncCall
            && \in_array(
                $this->getLowercaseMethodName($parentNode, 'left'),
                self::COUNT_NAME,
                true)
        ) {
            return $this->isAllowedLeftSideCountComparison($parentNode);
        }

        if ($parentNode->right instanceof Node\Expr\FuncCall
            && \in_array(
                $this->getLowercaseMethodName($parentNode, 'right'),
                self::COUNT_NAME,
                true)
        ) {
            return $this->isAllowedRightSideCountComparison($parentNode);
        }

        return true;
    }

    private function isAllowedLeftSideCountComparison(Node $node): bool
    {
        return $this->isSmallerThanComparison($node);
    }

    private function isAllowedRightSideCountComparison(Node $node): bool
    {
        return  $this->isGreaterThanComparison($node);
    }

    private function isComparison(Node $parentNode): bool
    {
        return $parentNode instanceof Node\Expr\BinaryOp\Identical
            || $parentNode instanceof Node\Expr\BinaryOp\NotIdentical
            || $parentNode instanceof Node\Expr\BinaryOp\Equal
            || $parentNode instanceof Node\Expr\BinaryOp\NotEqual
            || $this->isGreaterThanComparison($parentNode)
            || $this->isSmallerThanComparison($parentNode);
    }

    private function isGreaterThanComparison(Node $parentNode): bool
    {
        return $parentNode instanceof Node\Expr\BinaryOp\Greater
            || $parentNode instanceof Node\Expr\BinaryOp\GreaterOrEqual;
    }

    private function isSmallerThanComparison(Node $parentNode): bool
    {
        return $parentNode instanceof Node\Expr\BinaryOp\Smaller
            || $parentNode instanceof Node\Expr\BinaryOp\SmallerOrEqual;
    }

    private function getLowercaseMethodName(Node $node, string $part): string
    {
        return $node->{$part}->name->toLowerString();
    }
}
