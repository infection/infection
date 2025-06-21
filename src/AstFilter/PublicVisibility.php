<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
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

namespace Infection\AstFilter;

use function array_key_exists;
use function array_reverse;
use Infection\Mutation\Mutation;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Reflection\ClassReflection;
use PhpParser\Node;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class PublicVisibility implements AstPreFilter
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $methodsSeen = [];

    public function getMutatorClass(): string
    {
        return \Infection\Mutator\FunctionSignature\PublicVisibility::class;
    }

    public function visitNode(Node $node): void
    {
        if (!$node instanceof Node\Expr\MethodCall) {
            return;
        }
        /** @var ClassReflection|null $class */
        $class = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        if ($class === null) {
            return;
        }

        $methodCallChain = [];

        while (
            $node instanceof Node\Expr\MethodCall
            || $node instanceof Node\Expr\NullsafeMethodCall
            || $node instanceof Node\Expr\PropertyFetch
            || $node instanceof Node\Expr\NullsafePropertyFetch
        ) {
            $methodCallChain[] = $node;
            $node = $node->var;
        }

        if (
            !$node instanceof Node\Expr\Variable
            || $node->name !== 'this'
        ) {
            return;
        }

        $typeName = $class->getName();

        foreach (array_reverse($methodCallChain) as $chainElement) {
            if ($chainElement instanceof Node\Expr\MethodCall || $chainElement instanceof Node\Expr\NullsafeMethodCall) {
                $methodName = $chainElement->name;

                if (!$methodName instanceof Node\Identifier) {
                    return;
                }

                $this->methodsSeen[$typeName][$methodName->name] = true;

                try {
                    $reflectionMethod = new ReflectionMethod($typeName, $methodName->name);
                    $returnType = $reflectionMethod->getReturnType();

                    if (!$returnType instanceof ReflectionNamedType || $returnType->isBuiltin()) {
                        return;
                    }
                    $typeName = $this->resolveName($typeName, $returnType->getName());
                } catch (ReflectionException) {
                    return;
                }
            }

            if ($chainElement instanceof Node\Expr\PropertyFetch || $chainElement instanceof Node\Expr\NullsafePropertyFetch) {
                $propertyName = $chainElement->name;

                if (!$propertyName instanceof Node\Identifier) {
                    return;
                }

                try {
                    $propertyReflection = new ReflectionProperty(
                        $class->getName(),
                        $propertyName->name,
                    );

                    $propertyType = $propertyReflection->getType();

                    if ($propertyType instanceof ReflectionNamedType && !$propertyType->isBuiltin()) {
                        $typeName = $this->resolveName($typeName, $propertyType->getName());
                    }
                } catch (ReflectionException) {
                    return;
                    // If the reflection information does not exist, we cannot determine its type
                }
            }
        }
    }

    public function coversMutation(Mutation $mutation): bool
    {
        $node = $mutation->getMutatedNode()->unwrap();

        if (!$node instanceof Node\Stmt\ClassMethod) {
            return false;
        }

        /** @var ClassReflection $class */
        $class = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        return array_key_exists($class->getName(), $this->methodsSeen)
            && array_key_exists($node->name->name, $this->methodsSeen[$class->getName()]);
    }

    /**
     * @throws ReflectionException
     */
    private function resolveName(string $rootType, string $name): string
    {
        if ($name === 'self' || $name === 'static') {
            return $rootType;
        }

        if ($name === 'parent') {
            $reflectionClass = new ReflectionClass($rootType);
            $parent = $reflectionClass->getParentClass();

            if ($parent === false) {
                throw new ReflectionException('Parent class not found for ' . $rootType);
            }

            return $parent->getName();
        }

        return $name;
    }
}
