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

        return $parentNode->left instanceof Node\Expr\FuncCall && $parentNode->left->name->toString() === 'count';
    }
}
