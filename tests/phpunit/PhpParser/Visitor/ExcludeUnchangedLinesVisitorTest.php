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
use Infection\Differ\ChangedLinesRange;
use Infection\PhpParser\Visitor\ExcludeUnchangedLinesVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Source\Matcher\FakeSourceLineMatcher;
use Infection\Source\Matcher\SimpleSourceLineMatcher;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node\Name;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeUnchangedLinesVisitor::class)]
final class ExcludeUnchangedLinesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<int>|null $eligibleNodeIds
     * @param list<ChangedLinesRange> $changedLineRanges
     */
    #[DataProvider('nodeProvider')]
    public function test_it_marks_nodes_belonging_to_unchanged_lines_as_ineligible(
        string $code,
        ?array $eligibleNodeIds,
        array $changedLineRanges,
        string $expected,
    ): void {
        $nodes = $this->parse($code);

        $nodesById = $this->addIdsToNodes($nodes);
        $this->markNodesAsEligible(
            $nodesById,
            $eligibleNodeIds ?? array_keys($nodesById),
        );

        $sourceFile = '/path/to/virtual-test-file.php';

        $traverser = new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            new ExcludeUnchangedLinesVisitor(
                new SimpleSourceLineMatcher($changedLineRanges),
                $sourceFile,
            ),
            new MarkTraversedNodesAsVisitedVisitor(),
        );
        $traverser->traverse($nodes);

        $actual = $this->dumper->dump(
            $nodes,
            code: $code,
            dumpPositions: true,
            showLineNumbers: true,
        );

        $this->assertSame($expected, $actual);
    }

    public static function nodeProvider(): iterable
    {
        yield 'no eligible nodes and no changed lines' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                namespace Infection\Tests\Command\Debug\DumpAstCommand;

                final class EchoGreeter implements Greeter
                {
                    public function greet(): string
                    {
                        echo 'Hello world!';
                    }
                }

                PHP,
            [],
            [],
            <<<'AST'
                array(
                    0: Stmt_Declare[1:7 - 1:30](
                        declares: array(
                            0: DeclareItem[1:15 - 1:28](
                                key: Identifier[1:15 - 1:26](
                                    eligible: false
                                    endLine: 1
                                    nodeId: 2
                                    startLine: 1
                                )
                                value: Scalar_Int[1:28 - 1:28](
                                    eligible: false
                                    endLine: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    rawValue: 1
                                    startLine: 1
                                )
                                eligible: false
                                endLine: 1
                                nodeId: 1
                                startLine: 1
                            )
                        )
                        eligible: false
                        endLine: 1
                        nodeId: 0
                        startLine: 1
                    )
                    1: Stmt_Namespace[3:1 - 11:1](
                        name: Name[3:11 - 3:54](
                            eligible: false
                            endLine: 3
                            nodeId: 5
                            startLine: 3
                        )
                        stmts: array(
                            0: Stmt_Class[5:1 - 11:1](
                                name: Identifier[5:13 - 5:23](
                                    eligible: false
                                    endLine: 5
                                    nodeId: 7
                                    startLine: 5
                                )
                                implements: array(
                                    0: Name[5:36 - 5:42](
                                        eligible: false
                                        endLine: 5
                                        nodeId: 8
                                        startLine: 5
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod[7:5 - 10:5](
                                        name: Identifier[7:21 - 7:25](
                                            eligible: false
                                            endLine: 7
                                            nodeId: 10
                                            startLine: 7
                                        )
                                        returnType: Identifier[7:30 - 7:35](
                                            eligible: false
                                            endLine: 7
                                            nodeId: 11
                                            startLine: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo[9:9 - 9:28](
                                                exprs: array(
                                                    0: Scalar_String[9:14 - 9:27](
                                                        eligible: false
                                                        endLine: 9
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 13
                                                        rawValue: 'Hello world!'
                                                        startLine: 9
                                                    )
                                                )
                                                eligible: false
                                                endLine: 9
                                                nodeId: 12
                                                startLine: 9
                                            )
                                        )
                                        eligible: false
                                        endLine: 10
                                        nodeId: 9
                                        startLine: 7
                                    )
                                )
                                eligible: false
                                endLine: 11
                                nodeId: 6
                                startLine: 5
                            )
                        )
                        eligible: false
                        endLine: 11
                        kind: 1
                        nodeId: 4
                        startLine: 3
                    )
                )
                AST,
        ];

        yield 'all nodes are eligible and no changed lines' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                namespace Infection\Tests\Command\Debug\DumpAstCommand;

                final class EchoGreeter implements Greeter
                {
                    public function greet(): string
                    {
                        echo 'Hello world!';
                    }
                }

                PHP,
            null,
            [],
            <<<'AST'
                array(
                    0: Stmt_Declare[1:7 - 1:30](
                        declares: array(
                            0: DeclareItem[1:15 - 1:28](
                                key: Identifier[1:15 - 1:26](
                                    eligible: false
                                    endLine: 1
                                    nodeId: 2
                                    startLine: 1
                                )
                                value: Scalar_Int[1:28 - 1:28](
                                    eligible: false
                                    endLine: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    rawValue: 1
                                    startLine: 1
                                )
                                eligible: false
                                endLine: 1
                                nodeId: 1
                                startLine: 1
                            )
                        )
                        eligible: false
                        endLine: 1
                        nodeId: 0
                        startLine: 1
                    )
                    1: Stmt_Namespace[3:1 - 11:1](
                        name: Name[3:11 - 3:54](
                            eligible: false
                            endLine: 3
                            nodeId: 5
                            startLine: 3
                        )
                        stmts: array(
                            0: Stmt_Class[5:1 - 11:1](
                                name: Identifier[5:13 - 5:23](
                                    eligible: false
                                    endLine: 5
                                    nodeId: 7
                                    startLine: 5
                                )
                                implements: array(
                                    0: Name[5:36 - 5:42](
                                        eligible: false
                                        endLine: 5
                                        nodeId: 8
                                        startLine: 5
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod[7:5 - 10:5](
                                        name: Identifier[7:21 - 7:25](
                                            eligible: false
                                            endLine: 7
                                            nodeId: 10
                                            startLine: 7
                                        )
                                        returnType: Identifier[7:30 - 7:35](
                                            eligible: false
                                            endLine: 7
                                            nodeId: 11
                                            startLine: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo[9:9 - 9:28](
                                                exprs: array(
                                                    0: Scalar_String[9:14 - 9:27](
                                                        eligible: false
                                                        endLine: 9
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 13
                                                        rawValue: 'Hello world!'
                                                        startLine: 9
                                                    )
                                                )
                                                eligible: false
                                                endLine: 9
                                                nodeId: 12
                                                startLine: 9
                                            )
                                        )
                                        eligible: false
                                        endLine: 10
                                        nodeId: 9
                                        startLine: 7
                                    )
                                )
                                eligible: false
                                endLine: 11
                                nodeId: 6
                                startLine: 5
                            )
                        )
                        eligible: false
                        endLine: 11
                        kind: 1
                        nodeId: 4
                        startLine: 3
                    )
                )
                AST,
        ];

        yield 'no eligible nodes and all lines changed' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                namespace Infection\Tests\Command\Debug\DumpAstCommand;

                final class EchoGreeter implements Greeter
                {
                    public function greet(): string
                    {
                        echo 'Hello world!';
                    }
                }

                PHP,
            [],
            [ChangedLinesRange::create(1, 1000)],
            <<<'AST'
                array(
                    0: Stmt_Declare[1:7 - 1:30](
                        declares: array(
                            0: DeclareItem[1:15 - 1:28](
                                key: Identifier[1:15 - 1:26](
                                    eligible: true
                                    endLine: 1
                                    nodeId: 2
                                    startLine: 1
                                )
                                value: Scalar_Int[1:28 - 1:28](
                                    eligible: true
                                    endLine: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    rawValue: 1
                                    startLine: 1
                                )
                                eligible: true
                                endLine: 1
                                nodeId: 1
                                startLine: 1
                            )
                        )
                        eligible: true
                        endLine: 1
                        nodeId: 0
                        startLine: 1
                    )
                    1: Stmt_Namespace[3:1 - 11:1](
                        name: Name[3:11 - 3:54](
                            eligible: true
                            endLine: 3
                            nodeId: 5
                            startLine: 3
                        )
                        stmts: array(
                            0: Stmt_Class[5:1 - 11:1](
                                name: Identifier[5:13 - 5:23](
                                    eligible: true
                                    endLine: 5
                                    nodeId: 7
                                    startLine: 5
                                )
                                implements: array(
                                    0: Name[5:36 - 5:42](
                                        eligible: true
                                        endLine: 5
                                        nodeId: 8
                                        startLine: 5
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod[7:5 - 10:5](
                                        name: Identifier[7:21 - 7:25](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 10
                                            startLine: 7
                                        )
                                        returnType: Identifier[7:30 - 7:35](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 11
                                            startLine: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo[9:9 - 9:28](
                                                exprs: array(
                                                    0: Scalar_String[9:14 - 9:27](
                                                        eligible: true
                                                        endLine: 9
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 13
                                                        rawValue: 'Hello world!'
                                                        startLine: 9
                                                    )
                                                )
                                                eligible: true
                                                endLine: 9
                                                nodeId: 12
                                                startLine: 9
                                            )
                                        )
                                        eligible: true
                                        endLine: 10
                                        nodeId: 9
                                        startLine: 7
                                    )
                                )
                                eligible: true
                                endLine: 11
                                nodeId: 6
                                startLine: 5
                            )
                        )
                        eligible: true
                        endLine: 11
                        kind: 1
                        nodeId: 4
                        startLine: 3
                    )
                )
                AST,
        ];

        yield 'no eligible nodes and some lines changed' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                namespace Infection\Tests\Command\Debug\DumpAstCommand;

                final class EchoGreeter implements Greeter
                {
                    public function greet(): string
                    {
                        echo 'Hello world!';
                    }
                }

                PHP,
            [],
            [
                ChangedLinesRange::create(3, 5),
                ChangedLinesRange::create(6, 7),
            ],
            <<<'AST'
                array(
                    0: Stmt_Declare[1:7 - 1:30](
                        declares: array(
                            0: DeclareItem[1:15 - 1:28](
                                key: Identifier[1:15 - 1:26](
                                    eligible: false
                                    endLine: 1
                                    nodeId: 2
                                    startLine: 1
                                )
                                value: Scalar_Int[1:28 - 1:28](
                                    eligible: false
                                    endLine: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    rawValue: 1
                                    startLine: 1
                                )
                                eligible: false
                                endLine: 1
                                nodeId: 1
                                startLine: 1
                            )
                        )
                        eligible: false
                        endLine: 1
                        nodeId: 0
                        startLine: 1
                    )
                    1: Stmt_Namespace[3:1 - 11:1](
                        name: Name[3:11 - 3:54](
                            eligible: true
                            endLine: 3
                            nodeId: 5
                            startLine: 3
                        )
                        stmts: array(
                            0: Stmt_Class[5:1 - 11:1](
                                name: Identifier[5:13 - 5:23](
                                    eligible: true
                                    endLine: 5
                                    nodeId: 7
                                    startLine: 5
                                )
                                implements: array(
                                    0: Name[5:36 - 5:42](
                                        eligible: true
                                        endLine: 5
                                        nodeId: 8
                                        startLine: 5
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod[7:5 - 10:5](
                                        name: Identifier[7:21 - 7:25](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 10
                                            startLine: 7
                                        )
                                        returnType: Identifier[7:30 - 7:35](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 11
                                            startLine: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo[9:9 - 9:28](
                                                exprs: array(
                                                    0: Scalar_String[9:14 - 9:27](
                                                        eligible: false
                                                        endLine: 9
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 13
                                                        rawValue: 'Hello world!'
                                                        startLine: 9
                                                    )
                                                )
                                                eligible: false
                                                endLine: 9
                                                nodeId: 12
                                                startLine: 9
                                            )
                                        )
                                        eligible: true
                                        endLine: 10
                                        nodeId: 9
                                        startLine: 7
                                    )
                                )
                                eligible: true
                                endLine: 11
                                nodeId: 6
                                startLine: 5
                            )
                        )
                        eligible: true
                        endLine: 11
                        kind: 1
                        nodeId: 4
                        startLine: 3
                    )
                )
                AST,
        ];

        yield 'nodes are all eligible and all lines changed' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                namespace Infection\Tests\Command\Debug\DumpAstCommand;

                final class EchoGreeter implements Greeter
                {
                    public function greet(): string
                    {
                        echo 'Hello world!';
                    }
                }

                PHP,
            null,
            [ChangedLinesRange::create(1, 1000)],
            <<<'AST'
                array(
                    0: Stmt_Declare[1:7 - 1:30](
                        declares: array(
                            0: DeclareItem[1:15 - 1:28](
                                key: Identifier[1:15 - 1:26](
                                    eligible: true
                                    endLine: 1
                                    nodeId: 2
                                    startLine: 1
                                )
                                value: Scalar_Int[1:28 - 1:28](
                                    eligible: true
                                    endLine: 1
                                    kind: KIND_DEC (10)
                                    nodeId: 3
                                    rawValue: 1
                                    startLine: 1
                                )
                                eligible: true
                                endLine: 1
                                nodeId: 1
                                startLine: 1
                            )
                        )
                        eligible: true
                        endLine: 1
                        nodeId: 0
                        startLine: 1
                    )
                    1: Stmt_Namespace[3:1 - 11:1](
                        name: Name[3:11 - 3:54](
                            eligible: true
                            endLine: 3
                            nodeId: 5
                            startLine: 3
                        )
                        stmts: array(
                            0: Stmt_Class[5:1 - 11:1](
                                name: Identifier[5:13 - 5:23](
                                    eligible: true
                                    endLine: 5
                                    nodeId: 7
                                    startLine: 5
                                )
                                implements: array(
                                    0: Name[5:36 - 5:42](
                                        eligible: true
                                        endLine: 5
                                        nodeId: 8
                                        startLine: 5
                                    )
                                )
                                stmts: array(
                                    0: Stmt_ClassMethod[7:5 - 10:5](
                                        name: Identifier[7:21 - 7:25](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 10
                                            startLine: 7
                                        )
                                        returnType: Identifier[7:30 - 7:35](
                                            eligible: true
                                            endLine: 7
                                            nodeId: 11
                                            startLine: 7
                                        )
                                        stmts: array(
                                            0: Stmt_Echo[9:9 - 9:28](
                                                exprs: array(
                                                    0: Scalar_String[9:14 - 9:27](
                                                        eligible: true
                                                        endLine: 9
                                                        kind: KIND_SINGLE_QUOTED (1)
                                                        nodeId: 13
                                                        rawValue: 'Hello world!'
                                                        startLine: 9
                                                    )
                                                )
                                                eligible: true
                                                endLine: 9
                                                nodeId: 12
                                                startLine: 9
                                            )
                                        )
                                        eligible: true
                                        endLine: 10
                                        nodeId: 9
                                        startLine: 7
                                    )
                                )
                                eligible: true
                                endLine: 11
                                nodeId: 6
                                startLine: 5
                            )
                        )
                        eligible: true
                        endLine: 11
                        kind: 1
                        nodeId: 4
                        startLine: 3
                    )
                )
                AST,
        ];
    }

    public function test_it_does_not_check_ineligible_nodes(): void
    {
        $this->expectNotToPerformAssertions();

        $ineligibleNode = new Name('Ine');

        LabelNodesAsEligibleVisitor::markAsIneligible($ineligibleNode);

        $visitor = new ExcludeUnchangedLinesVisitor(
            new FakeSourceLineMatcher(),
            '/path/to/file',
        );
        $visitor->enterNode($ineligibleNode);
    }
}
