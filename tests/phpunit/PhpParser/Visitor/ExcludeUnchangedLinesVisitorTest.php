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

use function array_keys;
use Infection\PhpParser\Visitor\ExcludeUnchangedLinesVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeUnchangedLinesVisitor::class)]
final class ExcludeUnchangedLinesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int>|null $eligibleNodeIds
     */
    #[DataProvider('nodeProvider')]
    public function test_it_marks_nodes_belonging_to_unchanged_lines_as_ineligible(
        string $code,
        ?array $eligibleNodeIds,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        $this->markNodesAsEligible(
            $nodesById,
            $eligibleNodeIds ?? array_keys($nodesById),
        );

        $sourceFile = '/path/to/virtual-test-file.php';

        $sourceLineMatcherMock = $this->createStub(SourceLineMatcher::class);

        $traverser = new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            new ExcludeUnchangedLinesVisitor(
                $sourceLineMatcherMock,
                $sourceFile,
            ),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            dumpPositions: true,
            // TODO: use https://github.com/infection/infection/pull/3028
            dumpOtherAttributes: true,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no eligible nodes' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class ClassWithExcludedMethod {
                    function nonExcludedMethod() {}

                    function excludedMethod() {}
                }

                PHP,
            [],
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
    }
}
