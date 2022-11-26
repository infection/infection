<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor\Negation;

use Infection\PhpParser\Visitor\Negation\Driver\DriverInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class NegateAllSubExpressionsVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private DriverInterface $driver,
    ) {
    }

    public function beforeTraverse(array $nodes)
    {
        if (count($nodes) !== 1) {
            throw new \LogicException('This visitor supports only traverse for one node');
        }

        /** @var Node $node */
        $node = $nodes[0];

        if (!$node instanceof Node\Expr || !$this->driver->instanceOf($node)) {
            throw new \LogicException('This visitor does not support traverse of node type: ' . $node->getType());
        }

    }

    public function enterNode(Node $node)
    {
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }

    /**
     * @param Node\Expr $node
     */
    public function leaveNode(Node $node): Node\Expr
    {
        return $this->negateSubExpressions(
            $node,
            $node->getAttributes(),
        );
    }

    private function negateSubExpressions(Node\Expr $node, array $attributes = []): Node\Expr
    {
        if ($this->driver->instanceOf($node)) {
            return $this->driver->create(
                $this->negateSubExpressions($node->left),
                $this->negateSubExpressions($node->right),
                $attributes
            );
        }

        return $node instanceof Node\Expr\BooleanNot ? $node->expr : new Node\Expr\BooleanNot($node);
    }
}
