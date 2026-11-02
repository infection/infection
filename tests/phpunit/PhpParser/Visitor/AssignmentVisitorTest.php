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

namespace Infection\Tests\PhpParser\Visitor;

use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\AssignmentVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(AssignmentVisitor::class)]
final class AssignmentVisitorTest extends VisitorTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_identifies_assignments_and_other_side_effects(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parse('<?php ' . $code);

        $this->addIdsToNodes($nodes);

        (new NodeTraverser(
            new ParentConnectingVisitor(),
            new AssignmentVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $this->keepOnlyDesiredAttributes(
            $nodes,
            AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE,
            'containsAssignment',
            'containsSideEffect',
        );

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'nested assignment does not affect sibling statements' => [
            <<<'PHP'
                foo(bar($value = 1));
                foo();
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_FuncCall(
                                        name: Name(
                                            nodeId: 5
                                        )
                                        args: array(
                                            0: Arg(
                                                value: Expr_Assign(
                                                    var: Expr_Variable(
                                                        nodeId: 8
                                                    )
                                                    expr: Scalar_Int(
                                                        nodeId: 9
                                                    )
                                                    nodeId: 7
                                                )
                                                containsAssignment: true
                                                containsSideEffect: true
                                                nodeId: 6
                                            )
                                        )
                                        containsAssignment: true
                                        containsSideEffect: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 12
                            )
                            nodeId: 11
                        )
                        containsSideEffect: true
                        nodeId: 10
                    )
                )
                AST,
        ];

        yield 'compound assignment' => [
            <<<'PHP'
                foo($value += 1);
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_AssignOp_Plus(
                                        var: Expr_Variable(
                                            nodeId: 5
                                        )
                                        expr: Scalar_Int(
                                            nodeId: 6
                                        )
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'reference assignment' => [
            <<<'PHP'
                foo($value = &$other);
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_AssignRef(
                                        var: Expr_Variable(
                                            nodeId: 5
                                        )
                                        expr: Expr_Variable(
                                            nodeId: 6
                                        )
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'deferred closure retains assignment information for its own statements' => [
            <<<'PHP'
                foo(function () {
                    bar($value = 1);
                    bar();
                });
                foo();
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_Closure(
                                        stmts: array(
                                            0: Stmt_Expression(
                                                expr: Expr_FuncCall(
                                                    name: Name(
                                                        nodeId: 7
                                                    )
                                                    args: array(
                                                        0: Arg(
                                                            value: Expr_Assign(
                                                                var: Expr_Variable(
                                                                    nodeId: 10
                                                                )
                                                                expr: Scalar_Int(
                                                                    nodeId: 11
                                                                )
                                                                nodeId: 9
                                                            )
                                                            containsAssignment: true
                                                            containsSideEffect: true
                                                            nodeId: 8
                                                        )
                                                    )
                                                    containsAssignment: true
                                                    containsSideEffect: true
                                                    nodeId: 6
                                                )
                                                containsAssignment: true
                                                containsSideEffect: true
                                                nodeId: 5
                                            )
                                            1: Stmt_Expression(
                                                expr: Expr_FuncCall(
                                                    name: Name(
                                                        nodeId: 14
                                                    )
                                                    nodeId: 13
                                                )
                                                containsSideEffect: true
                                                nodeId: 12
                                            )
                                        )
                                        containsAssignment: true
                                        containsSideEffect: true
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 17
                            )
                            nodeId: 16
                        )
                        containsSideEffect: true
                        nodeId: 15
                    )
                )
                AST,
        ];

        yield 'deferred arrow function' => [
            <<<'PHP'
                foo(fn () => $value = 1);
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_ArrowFunction(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 6
                                            )
                                            expr: Scalar_Int(
                                                nodeId: 7
                                            )
                                            nodeId: 5
                                        )
                                        containsAssignment: true
                                        containsSideEffect: true
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'immediately invoked closure' => [
            <<<'PHP'
                foo((function () use (&$value) {
                    return $value = 1;
                })());
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_FuncCall(
                                        name: Expr_Closure(
                                            uses: array(
                                                0: ClosureUse(
                                                    var: Expr_Variable(
                                                        nodeId: 7
                                                    )
                                                    nodeId: 6
                                                )
                                            )
                                            stmts: array(
                                                0: Stmt_Return(
                                                    expr: Expr_Assign(
                                                        var: Expr_Variable(
                                                            nodeId: 10
                                                        )
                                                        expr: Scalar_Int(
                                                            nodeId: 11
                                                        )
                                                        nodeId: 9
                                                    )
                                                    containsAssignment: true
                                                    containsSideEffect: true
                                                    nodeId: 8
                                                )
                                            )
                                            containsAssignment: true
                                            containsSideEffect: true
                                            nodeId: 5
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'immediately invoked arrow function' => [
            <<<'PHP'
                foo((fn () => $state->value = 1)());
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_FuncCall(
                                        name: Expr_ArrowFunction(
                                            expr: Expr_Assign(
                                                var: Expr_PropertyFetch(
                                                    var: Expr_Variable(
                                                        nodeId: 8
                                                    )
                                                    name: Identifier(
                                                        nodeId: 9
                                                    )
                                                    nodeId: 7
                                                )
                                                expr: Scalar_Int(
                                                    nodeId: 10
                                                )
                                                nodeId: 6
                                            )
                                            containsAssignment: true
                                            containsSideEffect: true
                                            nodeId: 5
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'first-class callable does not execute the closure' => [
            <<<'PHP'
                foo((function () use (&$value) {
                    return $value = 1;
                })(...));
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_FuncCall(
                                        name: Expr_Closure(
                                            uses: array(
                                                0: ClosureUse(
                                                    var: Expr_Variable(
                                                        nodeId: 7
                                                    )
                                                    nodeId: 6
                                                )
                                            )
                                            stmts: array(
                                                0: Stmt_Return(
                                                    expr: Expr_Assign(
                                                        var: Expr_Variable(
                                                            nodeId: 10
                                                        )
                                                        expr: Scalar_Int(
                                                            nodeId: 11
                                                        )
                                                        nodeId: 9
                                                    )
                                                    containsAssignment: true
                                                    containsSideEffect: true
                                                    nodeId: 8
                                                )
                                            )
                                            containsAssignment: true
                                            containsSideEffect: true
                                            nodeId: 5
                                        )
                                        args: array(
                                            0: VariadicPlaceholder(
                                                nodeId: 12
                                            )
                                        )
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'invoked closure returning a deferred closure' => [
            <<<'PHP'
                foo((fn () => fn () => $state->value = 1)());
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_FuncCall(
                                        name: Expr_ArrowFunction(
                                            expr: Expr_ArrowFunction(
                                                expr: Expr_Assign(
                                                    var: Expr_PropertyFetch(
                                                        var: Expr_Variable(
                                                            nodeId: 9
                                                        )
                                                        name: Identifier(
                                                            nodeId: 10
                                                        )
                                                        nodeId: 8
                                                    )
                                                    expr: Scalar_Int(
                                                        nodeId: 11
                                                    )
                                                    nodeId: 7
                                                )
                                                containsAssignment: true
                                                containsSideEffect: true
                                                nodeId: 6
                                            )
                                            nodeId: 5
                                        )
                                        nodeId: 4
                                    )
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'anonymous class methods do not affect the enclosing call' => [
            <<<'PHP'
                foo(new class {
                    public function run()
                    {
                        bar($value = 1);
                    }
                });
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 2
                            )
                            args: array(
                                0: Arg(
                                    value: Expr_New(
                                        class: Stmt_Class(
                                            stmts: array(
                                                0: Stmt_ClassMethod(
                                                    name: Identifier(
                                                        nodeId: 7
                                                    )
                                                    stmts: array(
                                                        0: Stmt_Expression(
                                                            expr: Expr_FuncCall(
                                                                name: Name(
                                                                    nodeId: 10
                                                                )
                                                                args: array(
                                                                    0: Arg(
                                                                        value: Expr_Assign(
                                                                            var: Expr_Variable(
                                                                                nodeId: 13
                                                                            )
                                                                            expr: Scalar_Int(
                                                                                nodeId: 14
                                                                            )
                                                                            nodeId: 12
                                                                        )
                                                                        containsAssignment: true
                                                                        containsSideEffect: true
                                                                        nodeId: 11
                                                                    )
                                                                )
                                                                containsAssignment: true
                                                                containsSideEffect: true
                                                                nodeId: 9
                                                            )
                                                            containsAssignment: true
                                                            containsSideEffect: true
                                                            nodeId: 8
                                                        )
                                                    )
                                                    containsAssignment: true
                                                    containsSideEffect: true
                                                    nodeId: 6
                                                )
                                            )
                                            nodeId: 5
                                        )
                                        nodeId: 4
                                    )
                                    containsSideEffect: true
                                    nodeId: 3
                                )
                            )
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'expressions without side effects' => [
            <<<'PHP'
                $service->active;
                load(...);
                [fn () => update()];
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_PropertyFetch(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            name: Identifier(
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 6
                            )
                            args: array(
                                0: VariadicPlaceholder(
                                    nodeId: 7
                                )
                            )
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                    2: Stmt_Expression(
                        expr: Expr_Array(
                            items: array(
                                0: ArrayItem(
                                    value: Expr_ArrowFunction(
                                        expr: Expr_FuncCall(
                                            name: Name(
                                                nodeId: 13
                                            )
                                            nodeId: 12
                                        )
                                        containsSideEffect: true
                                        nodeId: 11
                                    )
                                    nodeId: 10
                                )
                            )
                            nodeId: 9
                        )
                        nodeId: 8
                    )
                )
                AST,
        ];

        yield 'calls and construction' => [
            <<<'PHP'
                [load()];
                new Service();
                ($service = load())->check(...);
                (function () {
                    update();
                })();
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Array(
                            items: array(
                                0: ArrayItem(
                                    value: Expr_FuncCall(
                                        name: Name(
                                            nodeId: 4
                                        )
                                        nodeId: 3
                                    )
                                    containsSideEffect: true
                                    nodeId: 2
                                )
                            )
                            containsSideEffect: true
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_New(
                            class: Name(
                                nodeId: 7
                            )
                            nodeId: 6
                        )
                        containsSideEffect: true
                        nodeId: 5
                    )
                    2: Stmt_Expression(
                        expr: Expr_MethodCall(
                            var: Expr_Assign(
                                var: Expr_Variable(
                                    nodeId: 11
                                )
                                expr: Expr_FuncCall(
                                    name: Name(
                                        nodeId: 13
                                    )
                                    nodeId: 12
                                )
                                containsSideEffect: true
                                nodeId: 10
                            )
                            name: Identifier(
                                nodeId: 14
                            )
                            args: array(
                                0: VariadicPlaceholder(
                                    nodeId: 15
                                )
                            )
                            containsAssignment: true
                            containsSideEffect: true
                            nodeId: 9
                        )
                        containsAssignment: true
                        containsSideEffect: true
                        nodeId: 8
                    )
                    3: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Expr_Closure(
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_FuncCall(
                                            name: Name(
                                                nodeId: 21
                                            )
                                            nodeId: 20
                                        )
                                        containsSideEffect: true
                                        nodeId: 19
                                    )
                                )
                                containsSideEffect: true
                                nodeId: 18
                            )
                            nodeId: 17
                        )
                        containsSideEffect: true
                        nodeId: 16
                    )
                )
                AST,
        ];

        yield 'increments and decrements' => [
            <<<'PHP'
                ++$value;
                $value++;
                --$value;
                $value--;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_PreInc(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_PostInc(
                            var: Expr_Variable(
                                nodeId: 5
                            )
                            nodeId: 4
                        )
                        containsSideEffect: true
                        nodeId: 3
                    )
                    2: Stmt_Expression(
                        expr: Expr_PreDec(
                            var: Expr_Variable(
                                nodeId: 8
                            )
                            nodeId: 7
                        )
                        containsSideEffect: true
                        nodeId: 6
                    )
                    3: Stmt_Expression(
                        expr: Expr_PostDec(
                            var: Expr_Variable(
                                nodeId: 11
                            )
                            nodeId: 10
                        )
                        containsSideEffect: true
                        nodeId: 9
                    )
                )
                AST,
        ];

        yield 'other side effects' => [
            <<<'PHP'
                yield $value;
                yield from $values;
                print $value;
                include $file;
                exit(1);
                throw $error;
                eval($code);
                `command`;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Yield(
                            value: Expr_Variable(
                                nodeId: 2
                            )
                            nodeId: 1
                        )
                        containsSideEffect: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_YieldFrom(
                            expr: Expr_Variable(
                                nodeId: 5
                            )
                            nodeId: 4
                        )
                        containsSideEffect: true
                        nodeId: 3
                    )
                    2: Stmt_Expression(
                        expr: Expr_Print(
                            expr: Expr_Variable(
                                nodeId: 8
                            )
                            nodeId: 7
                        )
                        containsSideEffect: true
                        nodeId: 6
                    )
                    3: Stmt_Expression(
                        expr: Expr_Include(
                            expr: Expr_Variable(
                                nodeId: 11
                            )
                            nodeId: 10
                        )
                        containsSideEffect: true
                        nodeId: 9
                    )
                    4: Stmt_Expression(
                        expr: Expr_Exit(
                            expr: Scalar_Int(
                                nodeId: 14
                            )
                            nodeId: 13
                        )
                        containsSideEffect: true
                        nodeId: 12
                    )
                    5: Stmt_Expression(
                        expr: Expr_Throw(
                            expr: Expr_Variable(
                                nodeId: 17
                            )
                            nodeId: 16
                        )
                        containsSideEffect: true
                        nodeId: 15
                    )
                    6: Stmt_Expression(
                        expr: Expr_Eval(
                            expr: Expr_Variable(
                                nodeId: 20
                            )
                            nodeId: 19
                        )
                        containsSideEffect: true
                        nodeId: 18
                    )
                    7: Stmt_Expression(
                        expr: Expr_ShellExec(
                            parts: array(
                                0: InterpolatedStringPart(
                                    nodeId: 23
                                )
                            )
                            nodeId: 22
                        )
                        containsSideEffect: true
                        nodeId: 21
                    )
                )
                AST,
        ];
    }
}
