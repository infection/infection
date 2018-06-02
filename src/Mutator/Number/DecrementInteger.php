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
    const COUNT_NAME = 'count';

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

        return !$this->isZeroComparedWithCountResult($node);
    }

    private function isZeroComparedWithCountResult(Node $node): bool
    {
        if ($node->value !== 0) {
            return false;
        }

        $parentNode = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if (!$parentNode instanceof Node\Expr\BinaryOp\Identical
            && !$parentNode instanceof Node\Expr\BinaryOp\NotIdentical
            && !$parentNode instanceof Node\Expr\BinaryOp\Equal
            && !$parentNode instanceof Node\Expr\BinaryOp\NotEqual
            && !$parentNode instanceof Node\Expr\BinaryOp\Greater
            && !$parentNode instanceof Node\Expr\BinaryOp\GreaterOrEqual) {
            return false;
        }

        $isLeftPartCount = $parentNode->left instanceof Node\Expr\FuncCall
            && $this->getLowercaseMethodName($parentNode, 'left') === self::COUNT_NAME;

        $isRightPartCount = $parentNode->right instanceof Node\Expr\FuncCall
            && $this->getLowercaseMethodName($parentNode, 'right') === self::COUNT_NAME;

        return $isLeftPartCount || $isRightPartCount;
    }

    private function getLowercaseMethodName(Node $node, string $part): string
    {
        return strtolower($node->{$part}->name->toString());
    }
}
