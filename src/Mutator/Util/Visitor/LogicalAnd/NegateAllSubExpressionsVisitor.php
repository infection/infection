<?php

declare(strict_types=1);

namespace Infection\Mutator\Util\Visitor\LogicalAnd;

use Infection\Mutator\Util\NegateExpression;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class NegateAllSubExpressionsVisitor extends NodeVisitorAbstract
{
    use NegateExpression;

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
    }

    public function leaveNode(Node $node): ?Node\Expr
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return $this->replace(
                $node,
                $node->getAttributes(),
            );
        }

        return null;
    }

    private function replace(Node\Expr $node, array $attributes = []): Node\Expr
    {
        if ($node instanceof Node\Expr\BinaryOp\BooleanAnd) {
            return new Node\Expr\BinaryOp\BooleanAnd(
                $this->replace($node->left),
                $this->replace($node->right),
                $attributes
            );
        }

        return $this->negateExpression($node);
    }
}
