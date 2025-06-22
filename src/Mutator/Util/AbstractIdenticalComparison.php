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

use function class_exists;
use function count;
use function gettype;
use function in_array;
use Infection\Mutator\Mutator;
use function is_numeric;
use function is_string;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\CallLike;
use ReflectionClassConstant;
use ReflectionConstant;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
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
    private static array $reflectionCache = [];

    protected function isSameTypeIdenticalComparison(Expr\BinaryOp\Equal|Expr\BinaryOp\Identical $comparison): bool
    {
        if ($this->isComparisonAgainstNonEmptyNonNumericString($comparison)) {
            return true;
        }

        if (
            $comparison->left instanceof Expr\FuncCall
            && $comparison->right instanceof Expr\FuncCall
            && $this->isSameTypeFuncCall($comparison->left, $comparison->right)
        ) {
            return true;
        }

        if (
            (
                $comparison->left instanceof Expr\FuncCall
                || $comparison->left instanceof Expr\StaticCall
                || $comparison->left instanceof Expr\ConstFetch
            )
            && (
                $comparison->right instanceof Node\Scalar
                || $comparison->right instanceof Expr\ConstFetch
                || $comparison->right instanceof Expr\ClassConstFetch
                || $comparison->right instanceof Expr\Array_
            )
            && $this->isSameTypeFuncCall($comparison->left, $comparison->right)
        ) {
            return true;
        }

        if (
            (
                $comparison->right instanceof Expr\FuncCall
                || $comparison->right instanceof Expr\StaticCall
                || $comparison->right instanceof Expr\ConstFetch
            )
            && ($comparison->left instanceof Node\Scalar
                || $comparison->left instanceof Expr\ConstFetch
                || $comparison->left instanceof Expr\ClassConstFetch
                || $comparison->left instanceof Expr\Array_
            )
            && $this->isSameTypeFuncCall($comparison->right, $comparison->left)
        ) {
            return true;
        }

        return false;
    }

    private function isSameTypeFuncCall(Expr\FuncCall|Expr\StaticCall|Expr\ConstFetch $call, Node\Scalar|Expr\ConstFetch|Expr\ClassConstFetch|Expr\FuncCall|Expr\Array_ $expr): bool
    {
        $narrowed = $this->getNarrowedReturnType($call, $expr);

        if ($narrowed === null) {
            return false; // unable to reflect the type
        }

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
            if (in_array($expr->name->toString(), ['true', 'false'], true)) {
                return $narrowed === 'bool';
            }

            $constValue = $this->getGlobalConstantValue($expr->name);

            if ($constValue === null) {
                return false; // unable to reflect the constant value
            }

            $constType = $this->getValueAsType($constValue);

            return $constType !== null && $constType === $narrowed;
        }

        if (
            $expr instanceof Expr\ClassConstFetch
            && $expr->class instanceof Node\Name
            && $expr->name instanceof Node\Identifier
        ) {
            $constValue = $this->getClassConstantValue(NameResolver::resolveName($expr->class), $expr->name);

            if ($constValue === null) {
                return false; // unable to reflect the constant value
            }

            $constType = $this->getValueAsType($constValue);

            return $constType !== null && $constType === $narrowed;
        }

        if ($expr instanceof Expr\Array_ && count($expr->items) === 0) {
            return $narrowed === 'array';
        }

        if ($expr instanceof Expr\FuncCall) {
            $exprReturnType = $this->getReturnType($expr);

            if (!$exprReturnType instanceof ReflectionNamedType) {
                return false;
            }

            return $narrowed === $this->narrowReturnType($exprReturnType, $expr);
        }

        return false;
    }

    private function getNarrowedReturnType(
        CallLike|Expr\ConstFetch $call,
        Node\Scalar|Expr\ConstFetch|Expr\ClassConstFetch|Expr\FuncCall|Expr\Array_ $expr,
    ): ?string {
        if (
            $call instanceof Expr\ConstFetch
        ) {
            $value = $this->getGlobalConstantValue($call->name);

            if ($value === null) {
                return null; // unable to reflect the constant value
            }

            return $this->getValueAsType($value);
        }

        $returnType = $this->getReturnType($call);

        if ($returnType === null) {
            return null; // unable to reflect the return type
        }

        return $this->narrowReturnType($returnType, $expr);
    }

    private function getReturnType(CallLike $call): ?ReflectionType
    {
        if ($call instanceof Expr\FuncCall) {
            if (!$call->name instanceof Node\Name) {
                return null;
            }
            $name = $call->name->toString();

            return self::$reflectionCache[$name] ?? $this->getFunctionReturnType($call->name);
        }

        if (
            $call instanceof Expr\StaticCall
            && $call->class instanceof Node\Name
            && $call->name instanceof Node\Identifier
        ) {
            $name = $call->class->toString() . '::' . $call->name->toString();

            return self::$reflectionCache[$name] ?? $this->getStaticMethodReturnType(NameResolver::resolveName($call->class), $call->name);
        }

        return null;
    }

    private function getFunctionReturnType(Node\Name $name): ?ReflectionType
    {
        try {
            $reflection = new ReflectionFunction($name->toString());

            return $reflection->getReturnType();
        } catch (ReflectionException) {
            // If the function does not exist, we cannot determine the return type
            return null;
        }
    }

    private function getStaticMethodReturnType(Node\Name\FullyQualified $class, Node\Identifier $method): ?ReflectionType
    {
        try {
            $reflection = new ReflectionMethod($class->toString(), $method->toString());

            return $reflection->getReturnType();
        } catch (ReflectionException) {
            // If the method does not exist, we cannot determine the return type
            return null;
        }
    }

    private function getGlobalConstantValue(Node\Name $name): mixed
    {
        if (!class_exists(ReflectionConstant::class)) {
            return null;
        }

        try {
            $reflection = new ReflectionConstant($name->toString());

            return $reflection->getValue();
        } catch (ReflectionException) {
            // If the no reflection info exist, we cannot determine the return type
            return null;
        }
    }

    private function getClassConstantValue(Node\Name\FullyQualified $class, Node\Identifier $name): mixed
    {
        try {
            $reflection = new ReflectionClassConstant($class->toString(), $name->toString());

            return $reflection->getValue();
        } catch (ReflectionException) {
            // If the no reflection info exist, we cannot determine the return type
            return null;
        }
    }

    /**
     * Maps types to identifiers known to php-src native ReflectionNamedType.
     */
    private function getValueAsType(mixed $value): ?string
    {
        $constType = gettype($value);

        if ($constType === 'integer') {
            return 'int';
        }

        if ($constType === 'string') {
            return 'string';
        }

        if ($constType === 'double') {
            return 'float';
        }

        if ($constType === 'boolean') {
            return 'bool';
        }

        if ($constType === 'array') {
            return 'array';
        }

        return null;
    }

    private function narrowReturnType(ReflectionType $returnType, Node\Scalar|Expr\ConstFetch|Expr\ClassConstFetch|Expr\FuncCall|Expr\Array_ $expr): ?string
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

    private function isComparisonAgainstNonEmptyNonNumericString(Expr\BinaryOp\Equal|Expr\BinaryOp\Identical $comparison): bool
    {
        return $this->isNonEmptyNonNumericStringExpr($comparison->left)
            || $this->isNonEmptyNonNumericStringExpr($comparison->right);
    }

    private function isNonEmptyNonNumericStringExpr(Expr $expr): bool
    {
        // you can't type juggle any expression type into a non-numeric&non-empty string
        // see https://github.com/phpstan/phpstan/issues/13120

        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value !== '' && !is_numeric($expr->value);
        }

        if ($expr instanceof Expr\ConstFetch) {
            $constValue = $this->getGlobalConstantValue($expr->name);

            return is_string($constValue) && $constValue !== '' && !is_numeric($constValue);
        }

        if (
            $expr instanceof Expr\ClassConstFetch
            && $expr->class instanceof Node\Name
            && $expr->name instanceof Node\Identifier
        ) {
            $constValue = $this->getClassConstantValue(NameResolver::resolveName($expr->class), $expr->name);

            return is_string($constValue) && $constValue !== '' && !is_numeric($constValue);
        }

        return false;
    }
}
