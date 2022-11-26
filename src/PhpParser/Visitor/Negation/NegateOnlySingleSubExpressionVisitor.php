<?php

declare(strict_types=1);

namespace Infection\PhpParser\Visitor\Negation;

use Infection\PhpParser\Visitor\Negation\Driver\DriverInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class NegateOnlySingleSubExpressionVisitor extends NodeVisitorAbstract
{
    private int $replaceExpressionAtIndex = 0;

    public function __construct(
        private DriverInterface $driver
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

        $subExpressionsToNegateCount = $this->countSubExpressionsToNegate($node);

        return array_fill(0, $subExpressionsToNegateCount, $node);
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
        $newNode = $this->negateSubExpression($node);

        $this->replaceExpressionAtIndex++;

        return $newNode;
    }

    public function afterTraverse(array $nodes)
    {
        $this->replaceExpressionAtIndex = 0;
    }

    private function countSubExpressionsToNegate(Node\Expr $node, int &$count = 0): int
    {
        if ($this->driver->instanceOf($node)) {
            $this->countSubExpressionsToNegate($node->left, $count);
            $this->countSubExpressionsToNegate($node->right, $count);
        } elseif ($this->canBeNegated($node)) {
            $count++;
        }

        return $count;
    }

    private function negateSubExpression(Node\Expr $node, int &$currentExpressionIndex = 0): Node\Expr
    {
        if ($this->driver->instanceOf($node)) {
            return $this->driver->create(
                $this->negateSubExpression($node->left, $currentExpressionIndex),
                $this->negateSubExpression($node->right, $currentExpressionIndex),
                $node->getAttributes(),
            );
        }

        if ($this->canBeNegated($node)) {
            if ($currentExpressionIndex === $this->replaceExpressionAtIndex) {
                $currentExpressionIndex++;

                return new Node\Expr\BooleanNot($node);
            }

            $currentExpressionIndex++;
        }

        return $node;
    }

    private function canBeNegated(Node\Expr $expr): bool
    {
        return $expr instanceof Node\Expr\FuncCall
            || $expr instanceof Node\Expr\MethodCall
            || $expr instanceof Node\Expr\StaticCall
            || $expr instanceof Node\Expr\Variable
            || $expr instanceof Node\Expr\ArrayDimFetch
            || $expr instanceof Node\Expr\ClassConstFetch;
    }
}
