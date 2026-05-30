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

namespace Infection\Tests\Architecture\PHPat\Selector\Support;

use function count;
use Infection\Framework\ClassName;
use PHPat\Selector\SelectorInterface;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt as Statement;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPStan\Reflection\ClassReflection;
use function Safe\file_get_contents;
use function str_starts_with;
use Webmozart\Assert\Assert;

final class HasTrivialImplementation implements SelectorInterface
{
    private const string UNEXPECTED_CALL_MESSAGE = 'Unexpected call.';

    public function getName(): string
    {
        return 'class with trivial implementation';
    }

    public function matches(ClassReflection $classReflection): bool
    {
        $class = self::getClassNode($classReflection);

        if ($class === null) {
            return false;
        }

        foreach ($class->stmts as $statement) {
            if (self::isNonBehavioralClassStatement($statement, $class)) {
                continue;
            }

            return false;
        }

        return true;
    }

    private static function getClassNode(ClassReflection $classReflection): ?Class_
    {
        $fileName = $classReflection->getFileName();

        if ($fileName === null) {
            return null;
        }

        $fileContent = file_get_contents($fileName);

        Assert::string($fileContent);

        $nodes = (new ParserFactory())->createForHostVersion()->parse($fileContent);

        if ($nodes === null) {
            return null;
        }

        $shortClassName = ClassName::getShortClassName($classReflection->getName());

        $class = (new NodeFinder())->findFirst(
            $nodes,
            static fn (Node $node): bool => $node instanceof Class_
                && !$node->isAnonymous()
                && $node->name?->toString() === $shortClassName,
        );

        return $class instanceof Class_ ? $class : null;
    }

    private static function isNonBehavioralClassStatement(Node $statement, Class_ $class): bool
    {
        if ($statement instanceof ClassConst
            || $statement instanceof Property
            || $statement instanceof TraitUse
        ) {
            return true;
        }

        return $statement instanceof ClassMethod
            && self::isNonBehavioralMethod($statement, $class);
    }

    private static function isNonBehavioralMethod(ClassMethod $method, Class_ $class): bool
    {
        return self::isEmptyMethod($method)
            || self::isPromotedPropertyConstructor($method)
            || self::isNoOpNullObjectMethod($method, $class)
            || self::isUnexpectedCallMethod($method);
    }

    private static function isEmptyMethod(ClassMethod $method): bool
    {
        return self::getExecutableStatements($method) === [];
    }

    private static function isPromotedPropertyConstructor(ClassMethod $method): bool
    {
        return self::isConstructor($method)
            && self::getExecutableStatements($method) === []
            && $method->params !== []
            && self::allParametersArePromotedProperties($method->params);
    }

    private static function isNoOpNullObjectMethod(ClassMethod $method, Class_ $class): bool
    {
        $statements = self::getExecutableStatements($method);

        return self::isNullClass($class)
            && count($statements) === 1
            && $statements[0] instanceof Return_
            && self::isSimpleReturnExpression($statements[0]->expr);
    }

    private static function isUnexpectedCallMethod(ClassMethod $method): bool
    {
        $statements = self::getExecutableStatements($method);

        return count($statements) === 1
            && $statements[0] instanceof Expression
            && $statements[0]->expr instanceof Throw_
            && $statements[0]->expr->expr instanceof New_
            && self::isUnexpectedCallException($statements[0]->expr->expr);
    }

    private static function isConstructor(ClassMethod $method): bool
    {
        return $method->name->toString() === '__construct';
    }

    private static function isNullClass(Class_ $class): bool
    {
        return $class->name !== null
            && str_starts_with($class->name->toString(), 'Null');
    }

    private static function isSimpleReturnExpression(?Node $expression): bool
    {
        return $expression === null
            || $expression instanceof Node\Expr\Array_
            || $expression instanceof Node\Expr\ConstFetch
            || $expression instanceof Node\Scalar;
    }

    private static function isUnexpectedCallException(New_ $new): bool
    {
        if (!$new->class instanceof Name) {
            return false;
        }

        if ($new->class->toString() !== 'DomainException') {
            return false;
        }

        return count($new->args) === 1
            && $new->args[0] instanceof Arg
            && $new->args[0]->value instanceof String_
            && $new->args[0]->value->value === self::UNEXPECTED_CALL_MESSAGE;
    }

    /**
     * @return list<Statement>
     */
    private static function getExecutableStatements(ClassMethod $method): array
    {
        if ($method->stmts === null) {
            return [];
        }

        $statements = [];

        foreach ($method->stmts as $statement) {
            if ($statement instanceof Nop) {
                continue;
            }

            $statements[] = $statement;
        }

        return $statements;
    }

    /**
     * @param array<int, Param> $parameters
     */
    private static function allParametersArePromotedProperties(array $parameters): bool
    {
        foreach ($parameters as $parameter) {
            if ($parameter->flags === 0) {
                return false;
            }
        }

        return true;
    }
}
