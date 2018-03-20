<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class ReflectionVisitor extends NodeVisitorAbstract
{
    const REFLECTION_CLASS_KEY = 'reflectionClass';
    const IS_INSIDE_FUNCTION_KEY = 'isInsideFunction';
    const IS_ON_FUNCTION_SIGNATURE = 'isOnFunctionSignature';
    const FUNCTION_SCOPE_KEY = 'functionScope';
    const FUNCTION_NAME = 'functionName';

    private $scopeStack = [];

    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var string
     */
    private $methodName;

    public function beforeTraverse(array $nodes)
    {
        $this->scopeStack = [];
        $this->reflectionClass = null;
        $this->methodName = null;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike && $node->fullyQualifiedClassName !== null) {
            $this->reflectionClass = new \ReflectionClass($node->fullyQualifiedClassName->toString());
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->methodName = $node->name->name;
        }

        $isInsideFunction = $this->isInsideFunction($node);

        if ($isInsideFunction) {
            $node->setAttribute(self::IS_INSIDE_FUNCTION_KEY, true);
        }

        if ($this->isPartOfFunctionSignature($node)) {
            $node->setAttribute(self::IS_ON_FUNCTION_SIGNATURE, true);
        }

        if ($this->isFunctionLikeNode($node)) {
            $this->scopeStack[] = $node;
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->reflectionClass);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        } elseif ($isInsideFunction) {
            $node->setAttribute(self::FUNCTION_SCOPE_KEY, $this->scopeStack[count($this->scopeStack) - 1]);
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->reflectionClass);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        }
    }

    public function leaveNode(Node $node)
    {
        if ($this->isFunctionLikeNode($node)) {
            array_pop($this->scopeStack);
        }
    }

    private function isPartOfFunctionSignature(Node $node): bool
    {
        if ($this->isFunctionLikeNode($node)) {
            return true;
        }

        if (!$node->hasAttribute(ParentConnectorVisitor::PARENT_KEY)) {
            return false;
        }

        $parent = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        return $parent instanceof Node\Param || $node instanceof Node\Param;
    }

    /**
     * Recursively determine whether the node is inside the function
     *
     * @param Node $node
     *
     * @return bool
     */
    private function isInsideFunction(Node $node): bool
    {
        if (!$node->hasAttribute(ParentConnectorVisitor::PARENT_KEY)) {
            return false;
        }

        $parent = $node->getAttribute(ParentConnectorVisitor::PARENT_KEY);

        if ($parent->getAttribute(self::IS_INSIDE_FUNCTION_KEY)) {
            return true;
        }

        if ($this->isFunctionLikeNode($parent)) {
            return true;
        }

        return $this->isInsideFunction($parent);
    }

    private function isFunctionLikeNode(Node $node): bool
    {
        $isClassMethod = $node instanceof Node\Stmt\ClassMethod;
        $isClosure = $node instanceof Node\Expr\Closure;

        return $isClassMethod || $isClosure;
    }
}
