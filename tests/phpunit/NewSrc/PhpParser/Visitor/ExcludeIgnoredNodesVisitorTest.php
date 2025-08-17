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

namespace Infection\Tests\NewSrc\PhpParser\Visitor;

use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use newSrc\AST\NodeVisitor\ExcludeIgnoredNodesVisitor;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use SplObjectStorage;
use function var_dump;

#[CoversClass(ExcludeIgnoredNodesVisitor::class)]
final class ExcludeIgnoredNodesVisitorTest extends VisitorTestCase
{
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(): void
    {
        $nodes = $this->createParser()->parse(
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    // @infection-ignore-all
                    function excludedMethod() {}
                }

                PHP,
        );

        /** @var Node\Stmt\Namespace_ $namespace */
        $namespace = $nodes[0];
        $namespaceName = $namespace->name;

        //        /** @var Node\Stmt\Expression $assignmentExpression */
        //        $assignmentExpression = $namespace->stmts[0];
        //        /** @var Node\Expr\Assign $assignmentStmt */
        //        $assignmentStmt = $assignmentExpression->expr;
        //        /** @var Node\Expr\Variable $variable */
        //        $variable = $assignmentStmt->var;
        //        /** @var Node\Expr\New_ $newStmt */
        //        $newStmt = $assignmentStmt->expr;
        //        /** @var Node\Name $newClassName */
        //        $newClassName = $newStmt->class;

        $expected = [
            ['beforeTraverse', [$nodes]],
            ['enterNode', [$namespace]],
            ['enterNode', [$namespaceName]],
            ['leaveNode', [$namespaceName]],
            //            ['enterNode', [$assignmentExpression]],
            //            ['enterNode', [$assignmentStmt]],
            //            ['enterNode', [$variable]],
            //            ['leaveNode', [$variable]],
            //            ['enterNode', [$newStmt]],
            //            ['enterNode', [$newClassName]],
            //            ['leaveNode', [$newClassName]],
            //            ['leaveNode', [$newStmt]],
            //            ['leaveNode', [$assignmentStmt]],
            //            ['leaveNode', [$assignmentExpression]],
            ['leaveNode', [$namespace]],
            ['afterTraverse', [$nodes]],
        ];

        $expected = <<<'AST_DUMP'
            Array &0 (
                0 => Array &1 (
                    0 => 'beforeTraverse'
                    1 => Array &2 (
                        0 => Array &3 (
                            0 => 'Stmt_Namespace(
                name: Name(
                    parts: array(
                        0: Infection
                        1: Tests
                        2: Virtual
                    )
                )
                stmts: array(
                    0: Stmt_Class(
                        attrGroups: array(
                        )
                        flags: 0
                        name: Identifier(
                            name: ClassWithExcludedMethod
                        )
                        extends: null
                        implements: array(
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: nonExcludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                            1: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: excludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                        )
                    )
                )
            )'
                        )
                    )
                )
                1 => Array &4 (
                    0 => 'enterNode'
                    1 => Array &5 (
                        0 => 'Stmt_Namespace(
                name: Name(
                    parts: array(
                        0: Infection
                        1: Tests
                        2: Virtual
                    )
                )
                stmts: array(
                    0: Stmt_Class(
                        attrGroups: array(
                        )
                        flags: 0
                        name: Identifier(
                            name: ClassWithExcludedMethod
                        )
                        extends: null
                        implements: array(
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: nonExcludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                            1: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: excludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                        )
                    )
                )
            )'
                    )
                )
                2 => Array &6 (
                    0 => 'enterNode'
                    1 => Array &7 (
                        0 => 'Name(
                parts: array(
                    0: Infection
                    1: Tests
                    2: Virtual
                )
            )'
                    )
                )
                3 => Array &8 (
                    0 => 'leaveNode'
                    1 => Array &9 (
                        0 => 'Name(
                parts: array(
                    0: Infection
                    1: Tests
                    2: Virtual
                )
            )'
                    )
                )
                4 => Array &10 (
                    0 => 'enterNode'
                    1 => Array &11 (
                        0 => 'Stmt_Class(
                attrGroups: array(
                )
                flags: 0
                name: Identifier(
                    name: ClassWithExcludedMethod
                )
                extends: null
                implements: array(
                )
                stmts: array(
                    0: Stmt_ClassMethod(
                        attrGroups: array(
                        )
                        flags: 0
                        byRef: false
                        name: Identifier(
                            name: nonExcludedMethod
                        )
                        params: array(
                        )
                        returnType: null
                        stmts: array(
                        )
                    )
                    1: Stmt_ClassMethod(
                        attrGroups: array(
                        )
                        flags: 0
                        byRef: false
                        name: Identifier(
                            name: excludedMethod
                        )
                        params: array(
                        )
                        returnType: null
                        stmts: array(
                        )
                    )
                )
            )'
                    )
                )
                5 => Array &12 (
                    0 => 'enterNode'
                    1 => Array &13 (
                        0 => 'Identifier(
                name: ClassWithExcludedMethod
            )'
                    )
                )
                6 => Array &14 (
                    0 => 'leaveNode'
                    1 => Array &15 (
                        0 => 'Identifier(
                name: ClassWithExcludedMethod
            )'
                    )
                )
                7 => Array &16 (
                    0 => 'enterNode'
                    1 => Array &17 (
                        0 => 'Stmt_ClassMethod(
                attrGroups: array(
                )
                flags: 0
                byRef: false
                name: Identifier(
                    name: nonExcludedMethod
                )
                params: array(
                )
                returnType: null
                stmts: array(
                )
            )'
                    )
                )
                8 => Array &18 (
                    0 => 'enterNode'
                    1 => Array &19 (
                        0 => 'Identifier(
                name: nonExcludedMethod
            )'
                    )
                )
                9 => Array &20 (
                    0 => 'leaveNode'
                    1 => Array &21 (
                        0 => 'Identifier(
                name: nonExcludedMethod
            )'
                    )
                )
                10 => Array &22 (
                    0 => 'leaveNode'
                    1 => Array &23 (
                        0 => 'Stmt_ClassMethod(
                attrGroups: array(
                )
                flags: 0
                byRef: false
                name: Identifier(
                    name: nonExcludedMethod
                )
                params: array(
                )
                returnType: null
                stmts: array(
                )
            )'
                    )
                )
                11 => Array &24 (
                    0 => 'leaveNode'
                    1 => Array &25 (
                        0 => 'Stmt_Class(
                attrGroups: array(
                )
                flags: 0
                name: Identifier(
                    name: ClassWithExcludedMethod
                )
                extends: null
                implements: array(
                )
                stmts: array(
                    0: Stmt_ClassMethod(
                        attrGroups: array(
                        )
                        flags: 0
                        byRef: false
                        name: Identifier(
                            name: nonExcludedMethod
                        )
                        params: array(
                        )
                        returnType: null
                        stmts: array(
                        )
                    )
                    1: Stmt_ClassMethod(
                        attrGroups: array(
                        )
                        flags: 0
                        byRef: false
                        name: Identifier(
                            name: excludedMethod
                        )
                        params: array(
                        )
                        returnType: null
                        stmts: array(
                        )
                    )
                )
            )'
                    )
                )
                12 => Array &26 (
                    0 => 'leaveNode'
                    1 => Array &27 (
                        0 => 'Stmt_Namespace(
                name: Name(
                    parts: array(
                        0: Infection
                        1: Tests
                        2: Virtual
                    )
                )
                stmts: array(
                    0: Stmt_Class(
                        attrGroups: array(
                        )
                        flags: 0
                        name: Identifier(
                            name: ClassWithExcludedMethod
                        )
                        extends: null
                        implements: array(
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: nonExcludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                            1: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: excludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                        )
                    )
                )
            )'
                    )
                )
                13 => Array &28 (
                    0 => 'afterTraverse'
                    1 => Array &29 (
                        0 => Array &30 (
                            0 => 'Stmt_Namespace(
                name: Name(
                    parts: array(
                        0: Infection
                        1: Tests
                        2: Virtual
                    )
                )
                stmts: array(
                    0: Stmt_Class(
                        attrGroups: array(
                        )
                        flags: 0
                        name: Identifier(
                            name: ClassWithExcludedMethod
                        )
                        extends: null
                        implements: array(
                        )
                        stmts: array(
                            0: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: nonExcludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                            1: Stmt_ClassMethod(
                                attrGroups: array(
                                )
                                flags: 0
                                byRef: false
                                name: Identifier(
                                    name: excludedMethod
                                )
                                params: array(
                                )
                                returnType: null
                                stmts: array(
                                )
                            )
                        )
                    )
                )
            )
            AST_DUMP;

        $recorder = new RecordTraverseVisitor();

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new ExcludeIgnoredNodesVisitor());
        $traverser->addVisitor($recorder);

        $traverser->traverse($nodes);

        $actual = $this->dumpRecordNodes($recorder->fetch());

        var_dump($actual);

        exit;

        $this->assertSame($expected, $actual);
    }

    //    public function test_it_does_not_toggle_ignorer_for_nodes_without_comments(): void
    //    {
    //        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
    //        $changingIgnorer
    //            ->expects($this->never())
    //            ->method($this->anything())
    //        ;
    //
    //        $ignoredNodes = new SplObjectStorage();
    //
    //        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);
    //
    //        $nodeWithoutComments = $this->createMock(Node::class);
    //        $nodeWithoutComments
    //            ->expects($this->once())
    //            ->method('getComments')
    //            ->willReturn([])
    //        ;
    //
    //        $visitor->enterNode($nodeWithoutComments);
    //
    //        $this->assertCount(0, $ignoredNodes);
    //    }
    //
    //    public function test_it_does_not_toggle_ignorer_for_nodes_with_comments_without_expected_annotation(): void
    //    {
    //        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
    //        $changingIgnorer
    //            ->expects($this->never())
    //            ->method($this->anything())
    //        ;
    //
    //        $ignoredNodes = new SplObjectStorage();
    //
    //        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);
    //
    //        $comment = $this->createMock(Comment::class);
    //        $comment
    //            ->expects($this->once())
    //            ->method('getText')
    //            ->willReturn('This is a test')
    //        ;
    //
    //        $nodeWithComments = $this->createMock(Node::class);
    //
    //        $nodeWithComments
    //            ->expects($this->once())
    //            ->method('getComments')
    //            ->willReturn([$comment])
    //        ;
    //
    //        $visitor->enterNode($nodeWithComments);
    //
    //        $this->assertCount(0, $ignoredNodes);
    //    }
    //
    //    public function test_it_toggles_ignorer_for_nodes_commented_with_expected_annotation(): void
    //    {
    //        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
    //        $changingIgnorer
    //            ->expects($this->once())
    //            ->method('startIgnoring')
    //        ;
    //
    //        $ignoredNodes = new SplObjectStorage();
    //
    //        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);
    //
    //        $comment = $this->createMock(Comment::class);
    //        $comment
    //            ->expects($this->once())
    //            ->method('getText')
    //            ->willReturn('@infection-ignore-all')
    //        ;
    //
    //        $nodeWithComments = $this->createMock(Node::class);
    //
    //        $nodeWithComments
    //            ->expects($this->once())
    //            ->method('getComments')
    //            ->willReturn([$comment])
    //        ;
    //
    //        $visitor->enterNode($nodeWithComments);
    //
    //        $this->assertCount(1, $ignoredNodes);
    //    }
    //
    //    public function test_it_stops_ignorer_when_leaving_node_it_started_ignoring_with(): void
    //    {
    //        $changingIgnorer = $this->createMock(ChangingIgnorer::class);
    //        $changingIgnorer
    //            ->expects($this->once())
    //            ->method('stopIgnoring')
    //        ;
    //
    //        $node = $this->createMock(Node::class);
    //
    //        $ignoredNodes = new SplObjectStorage();
    //        $ignoredNodes->attach($node);
    //
    //        $visitor = new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, $ignoredNodes);
    //
    //        $visitor->leaveNode($node);
    //
    //        $this->assertCount(0, $ignoredNodes);
    //    }
}
