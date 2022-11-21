<?php

declare(strict_types=1);

namespace Infection\Mutator\Util\Visitor\LogicalAnd;

use Infection\Mutator\Util\NegateExpression;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class CountSubExpressionsToNegateVisitor extends NodeVisitorAbstract
{
    use NegateExpression;

    private int $count = 0;

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            $this->visit($node);
        }
    }

    private function visit(Node\Expr $node): void
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            $this->visit($node->left);
            $this->visit($node->right);
        } elseif (!$this->isComparisonOrNegation($node)) {
            $this->count++;
        }
    }

    public function getCount()
    {
        return $this->count;
    }
}
