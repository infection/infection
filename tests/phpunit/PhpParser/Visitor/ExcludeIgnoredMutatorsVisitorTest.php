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

use Infection\Logger\MutationGeneration\PsrMutationGenerationDiagnosticLogger;
use Infection\PhpParser\Visitor\ExcludeIgnoredMutatorsVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function Pipeline\take;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function str_replace;

#[CoversClass(ExcludeIgnoredMutatorsVisitor::class)]
final class ExcludeIgnoredMutatorsVisitorTest extends VisitorTestCase
{
    /**
     * @param list<array{level: string, message: string, context: array<string, mixed>}> $expectedLogRecords
     */
    #[DataProvider('commentProvider')]
    public function test_it_reads_and_propagates_mutator_names_from_comments(
        string $code,
        string $expected,
        array $expectedLogRecords = [],
    ): void {
        $nodes = $this->parse($code);
        $logger = new TestLogger();

        $this->addIdsToNodes($nodes);

        (new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            new ParentConnectingVisitor(),
            new ExcludeIgnoredMutatorsVisitor(
                new PsrMutationGenerationDiagnosticLogger(
                    $logger,
                    false,
                ),
                '/source.php',
                ['CustomAlias'],
            ),
            new MarkTraversedNodesAsVisitedVisitor(),
        ))->traverse($nodes);

        $actual = $this->dumper->dump($nodes);

        $this->assertSame($expected, $actual);
        $this->assertSame($expectedLogRecords, $logger->records);
    }

    public static function commentProvider(): iterable
    {
        yield 'no comment' => [
            <<<'PHP'
                <?php

                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'unrelated comment' => [
            <<<'PHP'
                <?php

                // @other-annotation: FalseValue
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'colon syntax is not an annotation' => [
            <<<'PHP'
                <?php

                // @infection-ignore: FalseValue
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'ignore-all belongs to a different visitor' => [
            <<<'PHP'
                <?php

                // @infection-ignore-all
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'docblock' => [
            <<<'PHP'
                <?php

                /** @infection-ignore FalseValue */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'empty annotation does not hide a later valid annotation' => [
            <<<'PHP'
                <?php

                /**
                 * @infection-ignore
                 * @infection-ignore FalseValue
                 */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'No mutator names in @infection-ignore at /source.php:7.',
                    'context' => [],
                ],
            ],
        ];

        yield 'line comment' => [
            <<<'PHP'
                <?php

                // @infection-ignore FalseValue
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'hash comment' => [
            <<<'PHP'
                <?php

                # @infection-ignore FalseValue
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'block comment' => [
            <<<'PHP'
                <?php

                /* @infection-ignore FalseValue */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'comma-separated names and whitespace' => [
            <<<PHP
                <?php

                // @infection-ignore\t ArrayItemRemoval ,\tFalseValue\t
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: ArrayItemRemoval
                                    1: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: ArrayItemRemoval
                                1: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: ArrayItemRemoval
                            1: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'duplicates and empty entries' => [
            <<<'PHP'
                <?php

                /** @infection-ignore FalseValue, , FalseValue, */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'duplicate followed by a different name preserves a list' => [
            <<<'PHP'
                <?php

                // @infection-ignore FalseValue, FalseValue, ArrayItemRemoval
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                    1: ArrayItemRemoval
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                                1: ArrayItemRemoval
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                            1: ArrayItemRemoval
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'empty annotation' => [
            <<<'PHP'
                <?php

                /** @infection-ignore */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'No mutator names in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
            ],
        ];

        yield 'annotation with only commas and whitespace' => [
            <<<'PHP'
                <?php

                /** @infection-ignore , , */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'No mutator names in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
            ],
        ];

        yield 'names retain their case and namespace' => [
            <<<'PHP'
                <?php

                // @infection-ignore Acme\CustomMutator, falsevalue
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: Acme\CustomMutator
                                    1: falsevalue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: Acme\CustomMutator
                                1: falsevalue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: Acme\CustomMutator
                            1: falsevalue
                        )
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "Acme\\CustomMutator" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "falsevalue" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
            ],
        ];

        yield 'multiple annotations in a docblock with Windows line endings' => [
            str_replace(
                "\n",
                "\r\n",
                <<<'PHP'
                    <?php

                    /**
                     * @infection-ignore ArrayItemRemoval
                     * @infection-ignore FalseValue
                     */
                    return false;
                    PHP,
            ),
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: ArrayItemRemoval
                                    1: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: ArrayItemRemoval
                                1: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: ArrayItemRemoval
                            1: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'multiple comments on a node' => [
            <<<'PHP'
                <?php

                // @infection-ignore ArrayItemRemoval
                /* @infection-ignore FalseValue */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: ArrayItemRemoval
                                    1: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: ArrayItemRemoval
                                1: FalseValue
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: ArrayItemRemoval
                            1: FalseValue
                        )
                        nodeId: 0
                    )
                )
                AST,
        ];

        yield 'empty annotation does not consume the next docblock line' => [
            <<<'PHP'
                <?php

                /**
                 * @infection-ignore
                 * FalseValue
                 */
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'No mutator names in @infection-ignore at /source.php:7.',
                    'context' => [],
                ],
            ],
        ];

        yield 'nested exclusions are additive and do not leak to siblings' => [
            <<<'PHP'
                <?php

                /** @infection-ignore ArrayItemRemoval */
                function first() {
                    // @infection-ignore FalseValue
                    return [false];

                    return false;
                }

                /** @infection-ignore-all */
                function second() {
                    return false;
                }
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Function(
                        name: Identifier(
                            eligible: true
                            ignoredMutators: array(
                                0: ArrayItemRemoval
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        stmts: array(
                            0: Stmt_Return(
                                expr: Expr_Array(
                                    items: array(
                                        0: ArrayItem(
                                            value: Expr_ConstFetch(
                                                name: Name(
                                                    eligible: true
                                                    ignoredMutators: array(
                                                        0: ArrayItemRemoval
                                                        1: FalseValue
                                                    )
                                                    nodeId: 6
                                                    parent: nodeId(5)
                                                )
                                                eligible: true
                                                ignoredMutators: array(
                                                    0: ArrayItemRemoval
                                                    1: FalseValue
                                                )
                                                nodeId: 5
                                                parent: nodeId(4)
                                            )
                                            eligible: true
                                            ignoredMutators: array(
                                                0: ArrayItemRemoval
                                                1: FalseValue
                                            )
                                            nodeId: 4
                                            parent: nodeId(3)
                                        )
                                    )
                                    eligible: true
                                    ignoredMutators: array(
                                        0: ArrayItemRemoval
                                        1: FalseValue
                                    )
                                    kind: KIND_SHORT (2)
                                    nodeId: 3
                                    parent: nodeId(2)
                                )
                                eligible: true
                                ignoredMutators: array(
                                    0: ArrayItemRemoval
                                    1: FalseValue
                                )
                                nodeId: 2
                                parent: nodeId(0)
                            )
                            1: Stmt_Return(
                                expr: Expr_ConstFetch(
                                    name: Name(
                                        eligible: true
                                        ignoredMutators: array(
                                            0: ArrayItemRemoval
                                        )
                                        nodeId: 9
                                        parent: nodeId(8)
                                    )
                                    eligible: true
                                    ignoredMutators: array(
                                        0: ArrayItemRemoval
                                    )
                                    nodeId: 8
                                    parent: nodeId(7)
                                )
                                eligible: true
                                ignoredMutators: array(
                                    0: ArrayItemRemoval
                                )
                                nodeId: 7
                                parent: nodeId(0)
                            )
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: ArrayItemRemoval
                        )
                        nodeId: 0
                    )
                    1: Stmt_Function(
                        name: Identifier(
                            eligible: true
                            nodeId: 11
                            parent: nodeId(10)
                        )
                        stmts: array(
                            0: Stmt_Return(
                                expr: Expr_ConstFetch(
                                    name: Name(
                                        eligible: true
                                        nodeId: 14
                                        parent: nodeId(13)
                                    )
                                    eligible: true
                                    nodeId: 13
                                    parent: nodeId(12)
                                )
                                eligible: true
                                nodeId: 12
                                parent: nodeId(10)
                            )
                        )
                        eligible: true
                        nodeId: 10
                    )
                )
                AST,
        ];

        yield 'unknown names warn once on the annotated node' => [
            <<<'PHP'
                <?php

                // @infection-ignore FalseValue, CustomAlias, UnknownMutator, falsevalue, UnknownMutator, *, False
                return false;
                PHP,
            <<<'AST'
                array(
                    0: Stmt_Return(
                        expr: Expr_ConstFetch(
                            name: Name(
                                eligible: true
                                ignoredMutators: array(
                                    0: FalseValue
                                    1: CustomAlias
                                    2: UnknownMutator
                                    3: falsevalue
                                    4: *
                                    5: False
                                )
                                nodeId: 2
                                parent: nodeId(1)
                            )
                            eligible: true
                            ignoredMutators: array(
                                0: FalseValue
                                1: CustomAlias
                                2: UnknownMutator
                                3: falsevalue
                                4: *
                                5: False
                            )
                            nodeId: 1
                            parent: nodeId(0)
                        )
                        eligible: true
                        ignoredMutators: array(
                            0: FalseValue
                            1: CustomAlias
                            2: UnknownMutator
                            3: falsevalue
                            4: *
                            5: False
                        )
                        nodeId: 0
                    )
                )
                AST,
            [
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "UnknownMutator" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "falsevalue" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "*" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
                [
                    'level' => LogLevel::WARNING,
                    'message' => 'Unknown mutator "False" in @infection-ignore at /source.php:4.',
                    'context' => [],
                ],
            ],
        ];
    }

    /**
     * @param class-string<Node> $nodeClass
     * @param list<list<string>> $expectedIgnoredMutators
     */
    #[DataProvider('nodeExclusionProvider')]
    public function test_it_attaches_mutator_exclusions_to_the_expected_nodes(
        string $code,
        string $nodeClass,
        array $expectedIgnoredMutators,
    ): void {
        $nodes = $this->parse($code);

        (new NodeTraverser(
            new ParentConnectingVisitor(),
            new ExcludeIgnoredMutatorsVisitor(
                new PsrMutationGenerationDiagnosticLogger(
                    new TestLogger(),
                    false,
                ),
                '/source.php',
                [],
            ),
        ))->traverse($nodes);

        $selectedNodes = (new NodeFinder())->findInstanceOf(
            $nodes,
            $nodeClass,
        );

        $actual = take($selectedNodes)
            ->cast(ExcludeIgnoredMutatorsVisitor::getIgnoredMutators(...))
            ->toList()
        ;

        $this->assertSame($expectedIgnoredMutators, $actual);
    }

    public static function nodeExclusionProvider(): iterable
    {
        yield 'a trailing annotation on the middle statement excludes the following statement' => [
            <<<'PHP'
                <?php

                class Example {
                    public function values(): void {
                        $a = true;
                        $b = true; // @infection-ignore TrueValue
                        $c = true;
                    }
                }
                PHP,
            Expression::class,
            [
                [],
                [],
                ['TrueValue'],
            ],
        ];

        yield 'a trailing annotation excludes the following statement instead of its own statement' => [
            <<<'PHP'
                <?php

                class Example {
                    public function values(): void {
                        $a = true; // @infection-ignore TrueValue
                        $b = true;
                    }
                }
                PHP,
            Expression::class,
            [
                [],
                ['TrueValue'],
            ],
        ];

        yield 'a trailing annotation on the final statement does not exclude that statement' => [
            <<<'PHP'
                <?php

                class Example {
                    public function values(): void {
                        $a = true; // @infection-ignore TrueValue
                    }
                }
                PHP,
            Expression::class,
            [
                [],
            ],
        ];

        yield 'multiple mutators in a method docblock reach all descendant values' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore ArrayItemRemoval, FalseValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            ConstFetch::class,
            [
                ['ArrayItemRemoval', 'FalseValue'],
                ['ArrayItemRemoval', 'FalseValue'],
            ],
        ];

        yield 'nested class and method annotations add exclusions without leaking to siblings' => [
            <<<'PHP'
                <?php

                /** @infection-ignore ArrayItemRemoval */
                class Example {
                    /** @infection-ignore FalseValue */
                    public function first(): array {
                        return [false, true];
                    }

                    public function second(): array {
                        return [false, true];
                    }
                }

                class Outside {
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            Return_::class,
            [
                ['ArrayItemRemoval', 'FalseValue'],
                ['ArrayItemRemoval'],
                [],
            ],
        ];

        yield 'ignore-all does not alter inherited mutator names' => [
            <<<'PHP'
                <?php

                /** @infection-ignore ArrayItemRemoval */
                class Example {
                    public function ignored(): array {
                        // @infection-ignore-all
                        return [false, true];
                    }

                    public function outside(): bool {
                        return false;
                    }
                }
                PHP,
            Return_::class,
            [
                ['ArrayItemRemoval'],
                ['ArrayItemRemoval'],
            ],
        ];

        $ignoredArrayItem = <<<'PHP'
            <?php

            class Example {
                public function values(): array {
                    return [
                        // @infection-ignore FalseValue
                        false,
                        false,
                    ];
                }
            }
            PHP;

        yield 'an array item annotation reaches its descendants without leaking to its sibling' => [
            $ignoredArrayItem,
            ConstFetch::class,
            [
                ['FalseValue'],
                [],
            ],
        ];

        yield 'an array item annotation does not reach its parent array' => [
            $ignoredArrayItem,
            Array_::class,
            [
                [],
            ],
        ];

        $arrayItemWithIgnoredArrayMutator = <<<'PHP'
            <?php

            class Example {
                public function values(): array {
                    return [
                        // @infection-ignore ArrayItemRemoval
                        false,
                    ];
                }
            }
            PHP;

        yield 'an array mutator name on an item still reaches its descendants' => [
            $arrayItemWithIgnoredArrayMutator,
            ConstFetch::class,
            [
                ['ArrayItemRemoval'],
            ],
        ];

        yield 'an array mutator name on an item does not reach the parent array' => [
            $arrayItemWithIgnoredArrayMutator,
            Array_::class,
            [
                [],
            ],
        ];

        yield 'all listed mutator names reach every descendant value' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore ArrayItemRemoval, FalseValue, TrueValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            ConstFetch::class,
            [
                ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
                ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            ],
        ];

        yield 'commas do not require surrounding spaces' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore ArrayItemRemoval,FalseValue,TrueValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            Return_::class,
            [
                ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            ],
        ];

        yield 'extra spaces around names are allowed' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore   ArrayItemRemoval   ,   FalseValue  ,   TrueValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            Return_::class,
            [
                ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            ],
        ];
    }

    public function test_it_reads_ignored_mutators_from_a_node(): void
    {
        $node = new Nop();

        $this->assertSame(
            [],
            ExcludeIgnoredMutatorsVisitor::getIgnoredMutators($node),
        );

        $node->setAttribute(
            ExcludeIgnoredMutatorsVisitor::IGNORED_MUTATORS,
            ['FalseValue', 'CustomAlias'],
        );

        $this->assertSame(
            ['FalseValue', 'CustomAlias'],
            ExcludeIgnoredMutatorsVisitor::getIgnoredMutators($node),
        );
    }
}
