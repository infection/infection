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

namespace Infection\AstKiller;

use ReflectionException;
use function array_key_exists;
use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Reflection\ClassReflection;
use function is_string;
use PhpParser\Node;
use ReflectionNamedType;
use ReflectionProperty;

class PublicVisibility implements AstKiller
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $seenMethods = [];
    /**
     * @var array<string, array<string, bool>>
     */
    private array $seenProperties = [];

    public function getMutatorClass(): string
    {
        return \Infection\Mutator\FunctionSignature\PublicVisibility::class;
    }

    public function visit(Node $node): void
    {
        if (!$node instanceof Node\Expr\MethodCall) {
            return;
        }

        if (!$node->var instanceof Node\Expr\PropertyFetch) {
            return;
        }

        if (!$node->var->var instanceof Node\Expr\Variable) {
            return;
        }

        if (!is_string($node->var->var->name)) {
            return;
        }

        if ($node->var->var->name !== 'this') {
            return;
        }

        $propertyName = $node->var->name;

        if (!$propertyName instanceof Node\Identifier) {
            return;
        }

        $methodName = $node->name;

        if (!$methodName instanceof Node\Identifier) {
            return;
        }

        /** @var ClassReflection $class */
        $class = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        if (
            array_key_exists($class->getName(), $this->seenProperties)
            && array_key_exists($propertyName->name, $this->seenProperties[$class->getName()])
        ) {
            return;
        }
        $this->seenProperties[$class->getName()][$propertyName->name] = true;

        try {
            $propertyReflection = new ReflectionProperty(
                $class->getName(),
                $propertyName->name,
            );
        } catch (ReflectionException) {
            // If the reflection information does not exist, we cannot determine its type
            return;
        }

        $propertyType = $propertyReflection->getType();

        if (!$propertyType instanceof ReflectionNamedType) {
            return;
        }

        $this->seenMethods[$propertyType->getName()][$methodName->name] = true;
    }

    public function killsMutation(Mutation $mutation): bool
    {
        $node = $mutation->getMutatedNode()->unwrap();

        if (!$node instanceof Node\Stmt\ClassMethod) {
            return false;
        }

        /** @var ClassReflection $class */
        $class = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        return array_key_exists($class->getName(), $this->seenMethods)
            && array_key_exists($node->name->name, $this->seenMethods[$class->getName()]);
    }
}
