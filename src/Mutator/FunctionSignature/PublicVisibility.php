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

namespace Infection\Mutator\FunctionSignature;

use Infection\Mutator\Classification;
use Infection\Mutator\Definition;
use Infection\Mutator\Util\Mutator;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
final class PublicVisibility extends Mutator
{
    public static function getDefinition(): ?Definition
    {
        return new Definition(
            'Replaces the `public` method visibility keyword with `protected`.',
            Classification::SEMANTIC_REDUCTION,
            null
        );
    }

    /**
     * @param Node&ClassMethod $node
     *
     * @return ClassMethod
     */
    public function mutate(Node $node)
    {
        /* @var ClassMethod $node */
        return new ClassMethod(
            $node->name,
            [
                'flags' => ($node->flags & ~Class_::MODIFIER_PUBLIC) | Class_::MODIFIER_PROTECTED,
                'byRef' => $node->returnsByRef(),
                'params' => $node->getParams(),
                'returnType' => $node->getReturnType(),
                'stmts' => $node->getStmts(),
            ],
            $node->getAttributes()
        );
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof ClassMethod) {
            return false;
        }

        if (!$node->isPublic()) {
            return false;
        }

        if ($node->isMagic()) {
            return false;
        }

        return !$this->hasSamePublicParentMethod($node);
    }

    private function hasSamePublicParentMethod(ClassMethod $node): bool
    {
        /** @var ReflectionClass|null $reflection */
        $reflection = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        if (!$reflection instanceof ReflectionClass) {
            // assuming the worst where an interface has the same method
            return true;
        }

        try {
            return $reflection->getMethod($node->name->name)->getPrototype()->isPublic();
        } catch (ReflectionException $e) {
            return false;
        }
    }
}
