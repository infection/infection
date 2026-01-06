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

use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use SplObjectStorage;

#[CoversClass(IgnoreAllMutationsAnnotationReaderVisitor::class)]
final class IgnoreAllMutationsAnnotationReaderVisitorTest extends VisitorTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_marks_nodes_with_the_ignore_all_comment_as_ignored(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $changingIgnorer = new ChangingIgnorer();

        $traverser = new NodeTraverser(
            new IgnoreAllMutationsAnnotationReaderVisitor(
                $changingIgnorer,
                new SplObjectStorage(),
            ),
            new NonMutableNodesIgnorerVisitor([$changingIgnorer]),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        // Sanity check
        yield 'no code ignored' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    function excludedMethod() {}
                }

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier
                                    )
                                )
                            )
                        )
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'comment on a method' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    // @infection-ignore-all
                    function excludedMethod() {}
                }

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier
                                    )
                                    1: <skipped>
                                )
                            )
                        )
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'phpdoc on a method' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    /** @infection-ignore-all */
                    function excludedMethod() {}
                }

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier
                                    )
                                    1: <skipped>
                                )
                            )
                        )
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'comment on the class' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                // @infection-ignore-all
                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    function excludedMethod() {}
                }

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: <skipped>
                        )
                        kind: 1
                    )
                )
                AST,
        ];

        yield 'comment on an expression' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                $x = new Engine(
                    static fn () => 'first',
                    // @infection-ignore-all
                    static fn () => 'second',
                    static fn () => 'third',
                );

                PHP,
            <<<'AST'
                array(
                    0: Stmt_Namespace(
                        name: Name
                        stmts: array(
                            0: Stmt_Expression(
                                expr: Expr_Assign(
                                    var: Expr_Variable
                                    expr: Expr_New(
                                        class: Name
                                        args: array(
                                            0: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'first'
                                                    )
                                                )
                                            )
                                            1: <skipped>
                                            2: Arg(
                                                value: Expr_ArrowFunction(
                                                    expr: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'third'
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                        kind: 1
                    )
                )
                AST,
        ];
    }
}
