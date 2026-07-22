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

namespace Infection\Mutator\Operator;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\NodeAttributes;
use Infection\PhpParser\Visitor\ParentConnector;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\NodeFinder;

/**
 * Replaces `continue;` (or `continue N;`) with a return statement matching the
 * enclosing function signature: `return;`, `return null;`, or an empty default
 * (`0`, `0.0`, `''`, `false`, `[]`). Every skipped case is documented on the
 * private method implementing it.
 *
 * @internal
 *
 * @implements Mutator<Node\Stmt\Continue_>
 */
final class ContinueReturn implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a continue statement (`continue`) with a return statement, so the function
                exits instead of moving to the next iteration. The return value matches the signature:
                `return;` where legal, `return null;` for nullable return types, or an empty default
                (`0`, `0.0`, `''`, `false`, `[]`) for built-in value types.
                TXT,
            MutatorCategory::ORTHOGONAL_REPLACEMENT,
            <<<'TXT'
                To kill this mutant, use a fixture where iterations after the first skipped one matter:
                assert on results produced by later iterations or by the code executed after the loop.
                TXT,
            <<<'DIFF'
                foreach ($collection as $item) {
                    if ($condition) {
                -       continue;
                +       return;
                    }
                    $this->process($item);
                }
                $this->cleanUp();
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Stmt\Return_>
     */
    public function mutate(Node $node): iterable
    {
        $replacement = self::createReplacementReturn($node);

        // canMutate() only lets through jumps for which a replacement exists.
        if ($replacement === null) {
            return;
        }

        yield $replacement;
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Continue_) {
            return false;
        }

        $targetLoop = self::findTargetLoop($node);

        if ($targetLoop === null) {
            return false;
        }

        $replacement = self::createReplacementReturn($node);

        return $replacement !== null
            && !self::duplicatesContinueToBreakMutant(
                $replacement,
                self::findFirstStatementExecutedAfter($targetLoop),
            );
    }

    /**
     * Resolves the loop the `continue` acts upon. PHP counts enclosing switches
     * as jump levels too: `continue 2;` inside a switch inside a loop targets
     * that loop. Returns null when the jump targets a switch (there `continue`
     * is a warning-emitting synonym of `break`) or when the level exceeds the
     * actual nesting: a level zeroed on a switch stays negative from then on,
     * so no further loop can match and the walk falls through.
     *
     * @return Node\Stmt\Do_|Node\Stmt\For_|Node\Stmt\Foreach_|Node\Stmt\While_|null
     */
    private static function findTargetLoop(Node\Stmt\Continue_ $node): ?Node\Stmt
    {
        $level = self::resolveJumpLevel($node);

        if ($level === null) {
            return null;
        }

        $current = $node;

        while (($parent = ParentConnector::findParent($current)) !== null) {
            if (
                $parent instanceof Node\Stmt\Do_
                || $parent instanceof Node\Stmt\For_
                || $parent instanceof Node\Stmt\Foreach_
                || $parent instanceof Node\Stmt\While_
            ) {
                --$level;

                if ($level === 0) {
                    return $parent;
                }
            }

            if ($parent instanceof Node\Stmt\Switch_) {
                --$level;
            }

            $current = $parent;
        }

        return null;
    }

    /**
     * @return positive-int|null
     */
    private static function resolveJumpLevel(Node\Stmt\Continue_ $node): ?int
    {
        if ($node->num === null) {
            return 1;
        }

        // PHP only compiles positive integer literals as jump levels; anything
        // else the parser may have accepted cannot run and is not worth mutating.
        if ($node->num instanceof Node\Scalar\Int_ && $node->num->value >= 1) {
            return $node->num->value;
        }

        return null;
    }

    /**
     * The return statement to substitute for the jump, or null when the function
     * signature admits none.
     */
    private static function createReplacementReturn(Node $node): ?Node\Stmt\Return_
    {
        // ReflectionVisitor scopes class methods and closures only: in plain
        // functions and global code there is no signature to validate a return
        // against, so nothing is mutated there.
        $functionScope = ReflectionVisitor::findFunctionScope($node);

        if ($functionScope === null) {
            return null;
        }

        $returnType = $functionScope->getReturnType();
        $attributes = NodeAttributes::getAllExceptOriginalNode($node);

        if (
            $returnType === null
            || $returnType instanceof Node\Identifier && $returnType->toLowerString() === 'void'
        ) {
            return new Node\Stmt\Return_(null, $attributes);
        }

        // With any other declared type a bare `return;` only compiles in a
        // generator; a body with `yield` is compile-checked by PHP to declare a
        // generator-compatible type, so the type itself needs no inspection.
        if (self::isGenerator($functionScope)) {
            return new Node\Stmt\Return_(null, $attributes);
        }

        if (self::acceptsNull($returnType)) {
            return new Node\Stmt\Return_(new Node\Expr\ConstFetch(new Node\Name('null')), $attributes);
        }

        $defaultExpression = self::createDefaultReturnExpression($returnType);

        if ($defaultExpression !== null) {
            return new Node\Stmt\Return_($defaultExpression, $attributes);
        }

        return null;
    }

    private static function acceptsNull(Node\Identifier|Node\Name|Node\ComplexType $returnType): bool
    {
        if ($returnType instanceof Node\NullableType) {
            return true;
        }

        if ($returnType instanceof Node\Identifier) {
            $name = $returnType->toLowerString();

            return $name === 'mixed' || $name === 'null';
        }

        if ($returnType instanceof Node\UnionType) {
            foreach ($returnType->types as $type) {
                if ($type instanceof Node\Identifier && $type->toLowerString() === 'null') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * An "empty" literal for built-in value types. Class-like types get no
     * default on purpose: interfaces, abstract classes and enums cannot be
     * instantiated, and an object created without its constructor mostly dies
     * of uninitialized typed properties - always-killed noise either way.
     */
    private static function createDefaultReturnExpression(Node\Identifier|Node\Name|Node\ComplexType $returnType): ?Node\Expr
    {
        if (!$returnType instanceof Node\Identifier) {
            return null;
        }

        return match ($returnType->toLowerString()) {
            'int' => new Node\Scalar\Int_(0),
            'float' => new Node\Scalar\Float_(0.0),
            'string' => new Node\Scalar\String_(''),
            'bool', 'false' => new Node\Expr\ConstFetch(new Node\Name('false')),
            'true' => new Node\Expr\ConstFetch(new Node\Name('true')),
            'array', 'iterable' => new Node\Expr\Array_(),
            default => null,
        };
    }

    /**
     * The `continue -> break;` mutant of the Continue_ mutator stops the loop just
     * like ours does: the two only differ through what executes after the loop.
     * With nothing observable there - no statement at all, a return of the very
     * same value, or another jump whose control flow we do not follow - our mutant
     * would be a duplicate, wasting a test run.
     */
    private static function duplicatesContinueToBreakMutant(Node\Stmt\Return_ $replacement, ?Node\Stmt $statementAfterLoop): bool
    {
        if ($statementAfterLoop === null) {
            return true;
        }

        if ($statementAfterLoop instanceof Node\Stmt\Return_) {
            return self::isSameReturnValue($replacement->expr, $statementAfterLoop->expr);
        }

        return $statementAfterLoop instanceof Node\Stmt\Break_
            || $statementAfterLoop instanceof Node\Stmt\Continue_
            || $statementAfterLoop instanceof Node\Stmt\Goto_;
    }

    /**
     * Structural equality for the small literal family this mutator can emit:
     * enough to recognize that jumping to `return X;` equals returning X directly.
     */
    private static function isSameReturnValue(?Node\Expr $left, ?Node\Expr $right): bool
    {
        if (self::isNullValue($left)) {
            return self::isNullValue($right);
        }

        if ($right === null) {
            return false;
        }

        if ($left instanceof Node\Scalar\Int_ && $right instanceof Node\Scalar\Int_) {
            return $left->value === $right->value;
        }

        if ($left instanceof Node\Scalar\Float_ && $right instanceof Node\Scalar\Float_) {
            return $left->value === $right->value;
        }

        if ($left instanceof Node\Scalar\String_ && $right instanceof Node\Scalar\String_) {
            return $left->value === $right->value;
        }

        if ($left instanceof Node\Expr\ConstFetch && $right instanceof Node\Expr\ConstFetch) {
            return $left->name->toLowerString() === $right->name->toLowerString();
        }

        if ($left instanceof Node\Expr\Array_ && $right instanceof Node\Expr\Array_) {
            return $left->items === [] && $right->items === [];
        }

        return false;
    }

    private static function isNullValue(?Node\Expr $expression): bool
    {
        if ($expression === null) {
            return true;
        }

        return $expression instanceof Node\Expr\ConstFetch && $expression->name->toLowerString() === 'null';
    }

    private static function isGenerator(Node\FunctionLike $functionScope): bool
    {
        // A yield in a nested function-like makes that closure the generator, not
        // the enclosing function: only count yields attached to this very scope.
        $yield = (new NodeFinder())->findFirst(
            $functionScope->getStmts() ?? [],
            static fn (Node $node): bool => ($node instanceof Node\Expr\Yield_ || $node instanceof Node\Expr\YieldFrom)
                && ReflectionVisitor::findFunctionScope($node) === $functionScope,
        );

        return $yield !== null;
    }

    /**
     * Finds the first statement that runs once the loop is over, following the
     * execution path instead of the document order: leaving an if branch continues
     * after the whole construct, never in a sibling elseif/else/catch branch.
     */
    private static function findFirstStatementExecutedAfter(Node\Stmt $loop): ?Node\Stmt
    {
        $current = $loop;

        while (($parent = ParentConnector::findParent($current)) !== null) {
            $sibling = self::findNextSiblingStatement($parent, $current);

            if ($sibling !== null) {
                return $sibling;
            }

            // The function body is the outermost statement list: nothing running
            // after it belongs to this function.
            if ($parent instanceof Node\FunctionLike) {
                return null;
            }

            $current = $parent;
        }

        return null;
    }

    private static function findNextSiblingStatement(Node $parent, Node $child): ?Node\Stmt
    {
        $siblings = self::findStatementList($parent);

        if ($siblings === null) {
            return null;
        }

        $childSeen = false;

        foreach ($siblings as $sibling) {
            if ($sibling === $child) {
                $childSeen = true;

                continue;
            }

            if (!$childSeen) {
                continue;
            }

            // Nop is a comment placeholder, not executable code.
            if (!$sibling instanceof Node\Stmt\Nop) {
                return $sibling;
            }
        }

        return null;
    }

    /**
     * The canonical statement list a statement executes in. Alternative branch
     * containers (`If_::$elseifs`, `TryCatch::$catches`) are deliberately not
     * returned: control never falls from one branch into a sibling branch. The
     * `Switch_::$cases` list is returned because cases do fall through.
     *
     * @return Node\Stmt[]|null
     */
    private static function findStatementList(Node $node): ?array
    {
        if ($node instanceof Node\FunctionLike) {
            return $node->getStmts() ?? [];
        }

        if ($node instanceof Node\Stmt\Switch_) {
            return $node->cases;
        }

        if (
            $node instanceof Node\Stmt\Block
            || $node instanceof Node\Stmt\Case_
            || $node instanceof Node\Stmt\Catch_
            || $node instanceof Node\Stmt\Do_
            || $node instanceof Node\Stmt\Else_
            || $node instanceof Node\Stmt\ElseIf_
            || $node instanceof Node\Stmt\Finally_
            || $node instanceof Node\Stmt\For_
            || $node instanceof Node\Stmt\Foreach_
            || $node instanceof Node\Stmt\If_
            || $node instanceof Node\Stmt\TryCatch
            || $node instanceof Node\Stmt\While_
        ) {
            return $node->stmts;
        }

        return null;
    }
}
