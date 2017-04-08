<?php

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class InsideFunctionDetectorVisitor extends NodeVisitorAbstract
{
    const IS_INSIDE_FUNCTION_KEY = 'isInsideFunction';

    public function enterNode(Node $node)
    {
        if ($this->isInsideFunction($node)) {
            $node->setAttribute(self::IS_INSIDE_FUNCTION_KEY, true);
        }
    }

    /**
     * Recursively determine whether the node is inside the function
     *
     * @param Node $node
     * @return bool
     */
    private function isInsideFunction(Node $node) : bool
    {
        if (! $node->hasAttribute(ParentConnectorVisitor::PARENT_KEY)) {
            return false;
        }

        $parent = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if ($parent->getAttribute(self::IS_INSIDE_FUNCTION_KEY)) {
            return true;
        }

        $isFunction = $parent instanceof Node\Stmt\Function_;
        $isClassMethod = $parent instanceof Node\Stmt\ClassMethod;
        $isClosure = $parent instanceof Node\Expr\Closure;

        if ($isFunction || $isClassMethod || $isClosure) {
            return true;
        }

        return $this->isInsideFunction($parent);
    }
}
