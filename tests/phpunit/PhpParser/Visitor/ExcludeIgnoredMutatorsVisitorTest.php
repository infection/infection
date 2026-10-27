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

use Infection\PhpParser\Visitor\ExcludeIgnoredMutatorsVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MarkTraversedNodesAsVisitedVisitor;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
                $logger,
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

                // @infection-ignore FalseValue, CustomAlias, UnknownMutator, falsevalue, UnknownMutator, *
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
            ],
        ];
    }
}
