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

namespace Infection\Mutator\Removal;

use Infection\Mutator\Definition;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorCategory;
use Infection\Mutator\NodeAttributes;
use Infection\PhpParser\Visitor\AssignmentVisitor;
use function is_string;
use PhpParser\Node;
use function Pipeline\take;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @implements Mutator<Node\Stmt\Expression>
 */
final class MethodCallRemoval implements Mutator
{
    use GetMutatorName;

    public static function getDefinition(): Definition
    {
        return new Definition(
            'Removes the method call while preserving assignments in its receiver and arguments.',
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - $this->checkAccess($user = $this->findUser($id));
                + $user = $this->findUser($id);
                  return $user;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Stmt\Nop|list<Node\Stmt\Expression>>
     */
    public function mutate(Node $node): iterable
    {
        if (!AssignmentVisitor::containsAssignment($node->expr)) {
            yield new Node\Stmt\Nop();

            return;
        }

        yield self::removeCallPreservingAssignments($node);
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Stmt\Expression
            || !self::isMethodCall($node->expr)
        ) {
            return false;
        }

        if (!AssignmentVisitor::containsAssignment($node->expr)) {
            return true;
        }

        // Splitting a nullsafe chain would evaluate arguments even when its receiver is null.
        if (self::isInNullsafeChain($node->expr)) {
            return false;
        }

        foreach ($node->expr->args as $argument) {
            // Unpacking consumes iterators; callable placeholders do not evaluate arguments.
            if (!$argument instanceof Node\Arg || $argument->unpack) {
                return false;
            }
        }

        return true;
    }

    /**
     * @phpstan-assert-if-true Node\Expr\MethodCall|Node\Expr\NullsafeMethodCall|Node\Expr\StaticCall $expression
     */
    private static function isMethodCall(Node\Expr $expression): bool
    {
        return $expression instanceof Node\Expr\MethodCall
            || $expression instanceof Node\Expr\NullsafeMethodCall
            || $expression instanceof Node\Expr\StaticCall;
    }

    /**
     * @psalm-mutation-free
     *
     * @return non-empty-list<Node\Stmt\Expression>
     */
    private static function removeCallPreservingAssignments(Node\Stmt\Expression $node): array
    {
        $call = $node->expr;

        Assert::isInstanceOfAny(
            $call,
            [
                Node\Expr\MethodCall::class,
                Node\Expr\StaticCall::class,
            ],
            'Assignment-preserving removal supports only MethodCall and StaticCall nodes; got %s. Check MethodCallRemoval::canMutate().',
        );

        // Keep whole expressions: extracting nested assignments could change conditional
        // execution, and other operands can affect the values assigned later in the call.
        $statements = take(self::getEvaluatedExpressions($call))
            ->cast(
                static fn (Node\Expr $expression): Node\Stmt\Expression => new Node\Stmt\Expression(
                    $expression,
                    NodeAttributes::getAllExceptOriginalNode($expression),
                ),
            )
            ->toList()
        ;

        Assert::notEmpty(
            $statements,
            'A method call containing an assignment must retain at least one expression.',
        );

        // Keep the original statement's printing anchor and comments on the first expression.
        $firstStatement = clone $node;
        $firstStatement->expr = $statements[0]->expr;
        $statements[0] = $firstStatement;

        return $statements;
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr>
     */
    private static function getEvaluatedExpressions(Node\Expr\MethodCall|Node\Expr\StaticCall $call): iterable
    {
        $receiver = $call instanceof Node\Expr\StaticCall
            ? $call->class
            : $call->var;

        // Omit plain receiver variable reads; retain receiver expressions that may
        // have side effects. For example:
        //
        // ```php
        // $service->check($value = load());
        // ```
        //
        // becomes `$value = load();`, without a standalone `$service;` statement.
        // When the receiver is the result of a function call:
        //
        // ```php
        // createService()->check($value = load());
        // ```
        //
        // retain that call before the assignment to preserve its side effects:
        //
        // ```php
        // createService();
        // $value = load();
        // ```
        if (
            $receiver instanceof Node\Expr
            && !($receiver instanceof Node\Expr\Variable && is_string($receiver->name))
        ) {
            yield $receiver;
        }

        if ($call->name instanceof Node\Expr) {
            yield $call->name;
        }

        foreach ($call->getArgs() as $argument) {
            yield $argument->value;
        }
    }

    /**
     * Checks whether the call itself or an earlier operation in its receiver chain
     * uses PHP's nullsafe operator (`?->`). For example:
     *
     * ```php
     * $service?->prepare()->check($value = load());
     * ```
     *
     * A null $service skips check() and its arguments. Splitting that call into
     * separate expressions would execute the assignment unconditionally.
     *
     * Only receiver links are followed. Nullsafe expressions inside arguments remain
     * intact when those complete argument expressions are retained.
     */
    private static function isInNullsafeChain(Node $node): bool
    {
        while ($node instanceof Node\Expr) {
            if ($node instanceof Node\Expr\NullsafeMethodCall
                || $node instanceof Node\Expr\NullsafePropertyFetch
            ) {
                return true;
            }

            $node = match (true) {
                $node instanceof Node\Expr\MethodCall,
                $node instanceof Node\Expr\PropertyFetch,
                $node instanceof Node\Expr\ArrayDimFetch => $node->var,
                $node instanceof Node\Expr\StaticCall,
                $node instanceof Node\Expr\StaticPropertyFetch => $node->class,
                default => null,
            };
        }

        return false;
    }
}
