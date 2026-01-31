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

use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\Tests\TestingUtility\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LabelNodesAsEligibleVisitor::class)]
final class LabelNodeAsEligibleVisitorTest extends VisitorTestCase
{
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $traverser = new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            onlyVisitedNodes: false,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield [
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
                        name: Name(
                            ELIGIBLE: -1
                        )
                        stmts: array(
                            0: Stmt_Class(
                                name: Identifier(
                                    ELIGIBLE: -1
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: Identifier(
                                            ELIGIBLE: -1
                                        )
                                        ELIGIBLE: -1
                                    )
                                    1: Stmt_ClassMethod(
                                        name: Identifier(
                                            ELIGIBLE: -1
                                        )
                                        ELIGIBLE: -1
                                    )
                                )
                                ELIGIBLE: -1
                            )
                        )
                        kind: 1
                        ELIGIBLE: -1
                    )
                )
                AST,
        ];
    }
}
