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

namespace newSrc\AST;

use Infection\PhpParser\Visitor\ParentConnector;
use newSrc\TestFramework\Trace\Symbol\ClassReference;
use newSrc\TestFramework\Trace\Symbol\FunctionReference;
use newSrc\TestFramework\Trace\Symbol\MethodReference;
use newSrc\TestFramework\Trace\Symbol\NamespaceReference;
use newSrc\TestFramework\Trace\Symbol\Symbol;
use PhpParser\Node;
use function sprintf;
use Webmozart\Assert\Assert;

final class SymbolResolver
{
    public function tryToResolve(Node $node): ?Symbol
    {
        return match (true) {
            $node instanceof Node\Stmt\Namespace_ && $node->name !== null => new NamespaceReference(
                $node->name->toString(),
            ),
            $node instanceof Node\Stmt\Function_ => new FunctionReference(
                $node->name->toString(),
            ),
            $node instanceof Node\Stmt\Class_ => new ClassReference(
                $node->namespacedName->toString(),
            ),
            $node instanceof Node\Stmt\ClassMethod => new MethodReference(
                sprintf(
                    '%s::%s()',
                    self::getClassName($node)->toString(),
                    $node->name->toString(),
                ),
            ),
            default => null,
        };
    }

    private static function getClassName(Node\Stmt\ClassMethod $classMethod): Node\Name
    {
        /** @var Node\Stmt\Class_ $parent */
        $parent = ParentConnector::getParent($classMethod);
        Assert::isInstanceOf($parent, Node\Stmt\Class_::class);

        return $parent->namespacedName;
    }
}
