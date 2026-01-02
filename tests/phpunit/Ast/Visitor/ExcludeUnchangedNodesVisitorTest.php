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

namespace Infection\Tests\Ast\Visitor;

use Infection\Ast\Metadata\TraverseContext;
use Infection\Ast\NodeVisitor\ExcludeUnchangedNodesVisitor;
use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\FileSystem;
use Infection\Git\Git;
use Infection\Source\Matcher\GitDiffSourceLineMatcher;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\Tests\Ast\AstTestCase;
use Infection\Tests\Ast\Visitor\MarkTraversedNodesAsVisitedVisitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\TestFramework\Tracing\Trace\FakeTrace;
use const PHP_INT_MAX;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeUnchangedNodesVisitor::class)]
final class ExcludeUnchangedNodesVisitorTest extends AstTestCase
{
    /**
     * @param non-empty-array<string, list<ChangedLinesRange>> $changedLinesByPaths
     */
    #[DataProvider('nodeProvider')]
    public function test_it_annotates_excluded_nodes_and_stops_the_traversal(
        string $code,
        TraverseContext $context,
        array $changedLinesByPaths,
        string $expected,
    ): void {
        $nodes = $this->createParser()->parse($code);

        $traverser = new NodeTraverser(
            new ExcludeUnchangedNodesVisitor(
                $context,
                $this->createSourceLineMatcher($changedLinesByPaths),
            ),
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

                class Canary {
                    function firstMethod() {}

                    function secondMethod() {}
                }

                PHP,
            new TraverseContext(
                '/path/to/Canary.php',
                new FakeTrace(),
            ),
            [
                '/path/to/Canary.php' => [
                    ChangedLinesRange::forRange(0, PHP_INT_MAX),
                ],
            ],
            <<<'OUT'
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
                OUT,
        ];

        // This is an artificial case: if the file wasn't touched, it would not be part
        // of the diff in the first place.
        yield 'ignores all node if the file was not touched' => [
            <<<'PHP'
                <?php

                namespace Infection\Tests\Virtual;

                class Canary {
                    function firstMethod() {}

                    function secondMethod() {}
                }

                PHP,
            new TraverseContext(
                '/path/to/Canary.php',
                new FakeTrace(),
            ),
            [],
            <<<'OUT'
                array(
                    0: <skipped>
                )
                OUT,
        ];

        yield 'a node of the method was changed' => [
            <<<'PHP'
                <?php                               // L1
                                                    // L2
                namespace Infection\Tests\Virtual;  // L3
                                                    // L4
                class MyService {                   // L5
                    function firstMethod() {        // L6
                        echo 'changed-line';        // L7
                        echo 'unchanged-line1';     // L8
                    }                               // L9
                                                    // L10
                    function secondMethod() {       // L11
                        echo 'unchanged-line1';     // L12
                        echo 'unchanged-line2';     // L13
                    }                               // L14
                }                                   // L15

                PHP,
            new TraverseContext(
                '/path/to/MyService.php',
                new FakeTrace(),
            ),
            [
                '/path/to/MyService.php' => [
                    ChangedLinesRange::forLine(7),
                ],
            ],
            <<<'OUT'
                array(
                    0: Stmt_Namespace(
                        name: <skipped>
                        stmts: array(
                            0: Stmt_Class(
                                name: <skipped>
                                stmts: array(
                                    0: Stmt_ClassMethod(
                                        name: <skipped>
                                        stmts: array(
                                            0: Stmt_Echo(
                                                exprs: array(
                                                    0: Scalar_String(
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        rawValue: 'changed-line'
                                                    )
                                                )
                                            )
                                            1: <skipped>
                                            2: <skipped>
                                        )
                                    )
                                    1: <skipped>
                                    2: <skipped>
                                )
                            )
                            1: <skipped>
                        )
                        kind: 1
                    )
                )
                OUT,
        ];
    }

    /**
     * @param non-empty-array<string, list<ChangedLinesRange>> $changedLinesByPaths
     */
    private function createSourceLineMatcher(array $changedLinesByPaths): SourceLineMatcher
    {
        $gitMock = $this->createMock(Git::class);
        $gitMock
            ->method('getChangedLinesRangesByFileRelativePaths')
            ->willReturn($changedLinesByPaths);

        $fileSystemMock = $this->createMock(FileSystem::class);
        $fileSystemMock
            ->method('realPath')
            ->willReturnCallback(
                static fn (string $path) => $path,
            );

        return new GitDiffSourceLineMatcher(
            $gitMock,
            $fileSystemMock,
            'not-used',
            'not-used',
            ['not-used'],
        );
    }
}
