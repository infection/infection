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
    #[DataProvider('assignmentProvider')]
    public function test_it_identifies_nodes_containing_assignments(
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
        );

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function assignmentProvider(): iterable
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
                                                nodeId: 6
                                            )
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            nodeId: 1
                        )
                        containsAssignment: true
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 12
                            )
                            nodeId: 11
                        )
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
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            nodeId: 1
                        )
                        containsAssignment: true
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
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            nodeId: 1
                        )
                        containsAssignment: true
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
                                                            nodeId: 8
                                                        )
                                                    )
                                                    containsAssignment: true
                                                    nodeId: 6
                                                )
                                                containsAssignment: true
                                                nodeId: 5
                                            )
                                            1: Stmt_Expression(
                                                expr: Expr_FuncCall(
                                                    name: Name(
                                                        nodeId: 14
                                                    )
                                                    nodeId: 13
                                                )
                                                nodeId: 12
                                            )
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Expression(
                        expr: Expr_FuncCall(
                            name: Name(
                                nodeId: 17
                            )
                            nodeId: 16
                        )
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
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
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
                                                    nodeId: 8
                                                )
                                            )
                                            containsAssignment: true
                                            nodeId: 5
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            nodeId: 1
                        )
                        containsAssignment: true
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
                                            nodeId: 5
                                        )
                                        containsAssignment: true
                                        nodeId: 4
                                    )
                                    containsAssignment: true
                                    nodeId: 3
                                )
                            )
                            containsAssignment: true
                            nodeId: 1
                        )
                        containsAssignment: true
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
                                                    nodeId: 8
                                                )
                                            )
                                            containsAssignment: true
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
                                                nodeId: 6
                                            )
                                            nodeId: 5
                                        )
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
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
                                                                        nodeId: 11
                                                                    )
                                                                )
                                                                containsAssignment: true
                                                                nodeId: 9
                                                            )
                                                            containsAssignment: true
                                                            nodeId: 8
                                                        )
                                                    )
                                                    containsAssignment: true
                                                    nodeId: 6
                                                )
                                            )
                                            nodeId: 5
                                        )
                                        nodeId: 4
                                    )
                                    nodeId: 3
                                )
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];
    }
}
