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

use function array_map;
use function explode;
use function implode;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function rtrim;
use function strpos;
use function substr;

#[CoversClass(NextConnectingVisitor::class)]
final class NextConnectingVisitorTest extends VisitorTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_the_next_nodes(
        string $code,
        bool $ignorePhpComments,
        string $expected,
    ): void {
        $nodes = $this->parse(
            $ignorePhpComments
                ? self::removePhpComments($code)
                : $code,
        );

        $this->addIdsToNodes($nodes);
        (new NodeTraverser(
            new NextConnectingVisitor(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes, onlyVisitedNodes: false);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'it connects sequential statements' => [
            <<<'PHP'
                <?php

                $a = 1; // next = $b
                $b = 2; // next = $c
                $c = 3; // no next
                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                        next: nodeId(4)
                    )
                    1: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 6
                            )
                            expr: Scalar_Int(
                                rawValue: 2
                                kind: KIND_DEC (10)
                                nodeId: 7
                            )
                            nodeId: 5
                        )
                        nodeId: 4
                        next: nodeId(8)
                    )
                    2: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 10
                            )
                            expr: Scalar_Int(
                                rawValue: 3
                                kind: KIND_DEC (10)
                                nodeId: 11
                            )
                            nodeId: 9
                        )
                        nodeId: 8
                    )
                )
                AST,
        ];

        yield 'it handles functions and closures as boundaries' => [
            <<<'PHP'
                <?php

                $a = 1; // no next (function declaration breaks chain)

                function test() {
                    $b = 2; // next = $c
                    $c = 3; // no next (function body isolated)
                }

                $d = 4; // next = $closure1

                $closure1 = function () {   // next = $g (closure statement connects to next)
                    $e = 5; // next = $f
                    $f = 6; // no next (closure body isolated)
                };

                $g = 7; // next = $closure2

                $closure2 = fn () => $h = 8;    // next = $i (arrow function statement connects to next) ; $h has no next

                $i = 9; // no next

                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Function(
                        name: Identifier(
                            nodeId: 5
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 8
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 2
                                        kind: KIND_DEC (10)
                                        nodeId: 9
                                    )
                                    nodeId: 7
                                )
                                nodeId: 6
                                next: nodeId(10)
                            )
                            1: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 12
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 3
                                        kind: KIND_DEC (10)
                                        nodeId: 13
                                    )
                                    nodeId: 11
                                )
                                nodeId: 10
                            )
                        )
                        nodeId: 4
                    )
                    2: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 16
                            )
                            expr: Scalar_Int(
                                rawValue: 4
                                kind: KIND_DEC (10)
                                nodeId: 17
                            )
                            nodeId: 15
                        )
                        nodeId: 14
                        next: nodeId(18)
                    )
                    3: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 20
                            )
                            expr: Expr_Closure(
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 24
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 5
                                                kind: KIND_DEC (10)
                                                nodeId: 25
                                            )
                                            nodeId: 23
                                        )
                                        nodeId: 22
                                        next: nodeId(26)
                                    )
                                    1: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 28
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 6
                                                kind: KIND_DEC (10)
                                                nodeId: 29
                                            )
                                            nodeId: 27
                                        )
                                        nodeId: 26
                                    )
                                )
                                nodeId: 21
                            )
                            nodeId: 19
                        )
                        nodeId: 18
                        next: nodeId(30)
                    )
                    4: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 32
                            )
                            expr: Scalar_Int(
                                rawValue: 7
                                kind: KIND_DEC (10)
                                nodeId: 33
                            )
                            nodeId: 31
                        )
                        nodeId: 30
                        next: nodeId(34)
                    )
                    5: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 36
                            )
                            expr: Expr_ArrowFunction(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 39
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 8
                                        kind: KIND_DEC (10)
                                        nodeId: 40
                                    )
                                    nodeId: 38
                                )
                                nodeId: 37
                            )
                            nodeId: 35
                        )
                        nodeId: 34
                        next: nodeId(41)
                    )
                    6: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 43
                            )
                            expr: Scalar_Int(
                                rawValue: 9
                                kind: KIND_DEC (10)
                                nodeId: 44
                            )
                            nodeId: 42
                        )
                        nodeId: 41
                    )
                )
                AST,
        ];

        yield 'it skips nop statements' => [
            <<<'PHP'
                <?php

                $a = 1;
                $b = 2;
                // Comment
                /** Another comment */
                $c = 3;
                PHP,
            false,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                        next: nodeId(4)
                    )
                    1: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 6
                            )
                            expr: Scalar_Int(
                                rawValue: 2
                                kind: KIND_DEC (10)
                                nodeId: 7
                            )
                            nodeId: 5
                        )
                        nodeId: 4
                        next: nodeId(8)
                    )
                    2: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 10
                            )
                            expr: Scalar_Int(
                                rawValue: 3
                                kind: KIND_DEC (10)
                                nodeId: 11
                            )
                            nodeId: 9
                        )
                        nodeId: 8
                    )
                )
                AST,
        ];

        yield 'it handle class methods as functions and boundaries' => [
            <<<'PHP'
                <?php

                class Test {
                    public function foo() {
                        $a = 1; // next = $a
                        $b = 2; // no next
                    }

                    public function bar() {
                        $c = 3; // next = $d
                        $d = 4; // no next
                    }
                }
                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Class(
                        name: Identifier(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 3
                                )
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 6
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 1
                                                kind: KIND_DEC (10)
                                                nodeId: 7
                                            )
                                            nodeId: 5
                                        )
                                        nodeId: 4
                                        next: nodeId(8)
                                    )
                                    1: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 10
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 2
                                                kind: KIND_DEC (10)
                                                nodeId: 11
                                            )
                                            nodeId: 9
                                        )
                                        nodeId: 8
                                    )
                                )
                                nodeId: 2
                            )
                            1: Stmt_ClassMethod(
                                name: Identifier(
                                    nodeId: 13
                                )
                                stmts: array(
                                    0: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 16
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 3
                                                kind: KIND_DEC (10)
                                                nodeId: 17
                                            )
                                            nodeId: 15
                                        )
                                        nodeId: 14
                                        next: nodeId(18)
                                    )
                                    1: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 20
                                            )
                                            expr: Scalar_Int(
                                                rawValue: 4
                                                kind: KIND_DEC (10)
                                                nodeId: 21
                                            )
                                            nodeId: 19
                                        )
                                        nodeId: 18
                                    )
                                )
                                nodeId: 12
                            )
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it handles empty functions as boundaries' => [
            <<<'PHP'
                <?php

                $a = 1; // no next

                function empty_function() {
                }

                $b = 2; // no next
                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 2
                            )
                            expr: Scalar_Int(
                                rawValue: 1
                                kind: KIND_DEC (10)
                                nodeId: 3
                            )
                            nodeId: 1
                        )
                        nodeId: 0
                    )
                    1: Stmt_Function(
                        name: Identifier(
                            nodeId: 5
                        )
                        nodeId: 4
                    )
                    2: Stmt_Expression(
                        expr: Expr_Assign(
                            var: Expr_Variable(
                                nodeId: 8
                            )
                            expr: Scalar_Int(
                                rawValue: 2
                                kind: KIND_DEC (10)
                                nodeId: 9
                            )
                            nodeId: 7
                        )
                        nodeId: 6
                    )
                )
                AST,
        ];

        yield 'it connects return statements to next statements' => [
            <<<'PHP'
                <?php

                function hasMultipleReturns($condition) {
                    if ($condition) {   // $condition has next = return stmt
                        return 'early'; // next = $unreachable
                        $unreachable = true;    // next = $a
                    }

                    $a = 1; // next = return stmt
                    return 'normal';    // next = $afterReturn
                    $afterReturn = 2;   // no next
                }
                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Function(
                        name: Identifier(
                            nodeId: 1
                        )
                        params: array(
                            0: Param(
                                var: Expr_Variable(
                                    nodeId: 3
                                )
                                nodeId: 2
                            )
                        )
                        stmts: array(
                            0: Stmt_If(
                                cond: Expr_Variable(
                                    nodeId: 5
                                )
                                stmts: array(
                                    0: Stmt_Return(
                                        expr: Scalar_String(
                                            kind: KIND_SINGLE_QUOTED (1)
                                            rawValue: 'early'
                                            nodeId: 7
                                        )
                                        nodeId: 6
                                        next: nodeId(8)
                                    )
                                    1: Stmt_Expression(
                                        expr: Expr_Assign(
                                            var: Expr_Variable(
                                                nodeId: 10
                                            )
                                            expr: Expr_ConstFetch(
                                                name: Name(
                                                    nodeId: 12
                                                )
                                                nodeId: 11
                                            )
                                            nodeId: 9
                                        )
                                        nodeId: 8
                                        next: nodeId(13)
                                    )
                                )
                                nodeId: 4
                                next: nodeId(6)
                            )
                            1: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 15
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 1
                                        kind: KIND_DEC (10)
                                        nodeId: 16
                                    )
                                    nodeId: 14
                                )
                                nodeId: 13
                                next: nodeId(17)
                            )
                            2: Stmt_Return(
                                expr: Scalar_String(
                                    kind: KIND_SINGLE_QUOTED (1)
                                    rawValue: 'normal'
                                    nodeId: 18
                                )
                                nodeId: 17
                                next: nodeId(19)
                            )
                            3: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 21
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 2
                                        kind: KIND_DEC (10)
                                        nodeId: 22
                                    )
                                    nodeId: 20
                                )
                                nodeId: 19
                            )
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'it does not connect the last return statement' => [
            <<<'PHP'
                <?php

                function lastReturn() {
                    $a = 1;     // next = return stmt
                    return $a;  // no next
                }
                PHP,
            true,
            <<<'AST'
                array(
                    0: Stmt_Function(
                        name: Identifier(
                            nodeId: 1
                        )
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable(
                                        nodeId: 4
                                    )
                                    expr: Scalar_Int(
                                        rawValue: 1
                                        kind: KIND_DEC (10)
                                        nodeId: 5
                                    )
                                    nodeId: 3
                                )
                                nodeId: 2
                                next: nodeId(6)
                            )
                            1: Stmt_Return(
                                expr: Expr_Variable(
                                    nodeId: 7
                                )
                                nodeId: 6
                            )
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];
    }

    private function removePhpComments(string $code): string
    {
        return implode(
            "\n",
            array_map(
                self::removePhpComment(...),
                explode("\n", $code),
            ),
        );
    }

    private function removePhpComment(string $line): string
    {
        $position = strpos($line, '// ');

        return $position === false
            ? $line
            : rtrim(substr($line, 0, $position));
    }
}
