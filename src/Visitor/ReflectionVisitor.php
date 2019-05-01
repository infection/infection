<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Visitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class ReflectionVisitor extends NodeVisitorAbstract
{
    public const REFLECTION_CLASS_KEY = 'reflectionClass';
    public const IS_INSIDE_FUNCTION_KEY = 'isInsideFunction';
    public const IS_ON_FUNCTION_SIGNATURE = 'isOnFunctionSignature';
    public const FUNCTION_SCOPE_KEY = 'functionScope';
    public const FUNCTION_NAME = 'functionName';

    /**
     * @var Node\Expr\Closure[]|Node\Stmt\ClassMethod[]|Node[]
     */
    private $functionScopeStack = [];

    /**
     * @var \ReflectionClass[]|null[]
     */
    private $classScopeStack = [];

    /**
     * @var string|null
     */
    private $methodName;

    public function beforeTraverse(array $nodes): void
    {
        $this->functionScopeStack = [];
        $this->classScopeStack = [];
        $this->methodName = null;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            if ($attribute = $node->getAttribute(FullyQualifiedClassNameVisitor::FQN_KEY)) {
                $this->classScopeStack[] = new \ReflectionClass($attribute);
            } else {
                // Anonymous class
                $this->classScopeStack[] = null;
            }
        }

        // No need to traverse outside of classes
        if (\count($this->classScopeStack) === 0) {
            return null;
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->methodName = $node->name->name;
        }

        $isInsideFunction = $this->isInsideFunction($node);

        if ($isInsideFunction) {
            $node->setAttribute(self::IS_INSIDE_FUNCTION_KEY, true);
        } elseif ($node instanceof Node\Stmt\Function_) {
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        if ($this->isPartOfFunctionSignature($node)) {
            $node->setAttribute(self::IS_ON_FUNCTION_SIGNATURE, true);
        }

        if ($this->isFunctionLikeNode($node)) {
            $this->functionScopeStack[] = $node;
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->classScopeStack[\count($this->classScopeStack) - 1]);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        } elseif ($isInsideFunction) {
            $node->setAttribute(self::FUNCTION_SCOPE_KEY, $this->functionScopeStack[\count($this->functionScopeStack) - 1]);
            $node->setAttribute(self::REFLECTION_CLASS_KEY, $this->classScopeStack[\count($this->classScopeStack) - 1]);
            $node->setAttribute(self::FUNCTION_NAME, $this->methodName);
        }
    }

    public function leaveNode(Node $node): void
    {
        if ($this->isFunctionLikeNode($node)) {
            array_pop($this->functionScopeStack);
        }

        if ($node instanceof  Node\Stmt\ClassLike) {
            array_pop($this->classScopeStack);
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
        if ($node instanceof Node\Stmt\ClassMethod) {
            return true;
        }

        if ($node instanceof Node\Expr\Closure) {
            return true;
        }

        return false;
    }
}
