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

namespace Infection\Mutator\Util;

use function array_key_exists;
use function count;
use function in_array;
use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
abstract class AbstractIdenticalComparison implements Mutator
{
    /**
     * @var array<string, ReflectionType|null>
     */
    private array $reflectionCache = [];

    /**
     * @param Node\Scalar|Expr\ConstFetch|Expr\FuncCall $expr
     */
    protected function isSameTypeIdenticalComparison(Expr\FuncCall $call, Expr $expr): bool
    {
        $returnType = $this->getReturnType($call);

        if ($returnType === null) {
            return false;
        }

        $narrowed = $this->narrowReturnType($returnType, $expr);

        if ($expr instanceof Node\Scalar\Int_) {
            return $narrowed === 'int';
        }

        if ($expr instanceof Node\Scalar\String_) {
            return $narrowed === 'string';
        }

        if ($expr instanceof Node\Scalar\Float_) {
            return $narrowed === 'float';
        }

        if ($expr instanceof Expr\ConstFetch) {
            return $narrowed === 'bool' && in_array($expr->name->toString(), ['true', 'false'], true);
        }

        if ($expr instanceof Expr\FuncCall) {
            $exprReturnType = $this->getReturnType($expr);

            if ($exprReturnType === null) {
                return false;
            }

            if (
                !$returnType instanceof ReflectionNamedType
                || !$exprReturnType instanceof ReflectionNamedType
            ) {
                return false;
            }

            return $returnType->getName() === $exprReturnType->getName();
        }

        return false;
    }

    private function getReturnType(Expr\FuncCall $call): ?ReflectionType
    {
        if (!$call->name instanceof Node\Name) {
            return null;
        }

        $name = $call->name->toString();

        if (array_key_exists($name, $this->reflectionCache)) {
            return $this->reflectionCache[$name];
        }

        try {
            $reflection = new ReflectionFunction($name);

            return $this->reflectionCache[$name] = $reflection->getReturnType();
        } catch (ReflectionException) {
            // If the function does not exist, we cannot determine the return type
            return $this->reflectionCache[$name] = null;
        }
    }

    /**
     * @param Node\Scalar|Expr\ConstFetch|Expr\FuncCall $expr
     */
    private function narrowReturnType(ReflectionType $returnType, Expr $expr): ?string
    {
        if ($returnType instanceof ReflectionNamedType) {
            return $returnType->getName();
        }

        $remainingType = [];

        if ($returnType instanceof ReflectionUnionType) {
            if (
                $expr instanceof Node\Scalar\Int_
                || $expr instanceof Node\Scalar\String_
                || $expr instanceof Node\Scalar\Float_
            ) {
                $exprValue = $expr->value;
            } elseif ($expr instanceof Expr\ConstFetch && $expr->name->toString() === 'true') {
                $exprValue = true;
            } elseif ($expr instanceof Expr\ConstFetch && $expr->name->toString() === 'false') {
                $exprValue = false;
            } else {
                return null; // cannot narrow down the type
            }

            foreach ($returnType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    if ($type->getName() === 'false') {
                        // non-falsy value eliminates bool-false
                        if ($exprValue) { // @phpstan-ignore if.condNotBoolean
                            continue;
                        }
                    }

                    $remainingType[] = $type->getName();

                    continue;
                }

                return null;
            }
        }

        if (count($remainingType) === 1) {
            return $remainingType[0];
        }

        return null;
    }
}
