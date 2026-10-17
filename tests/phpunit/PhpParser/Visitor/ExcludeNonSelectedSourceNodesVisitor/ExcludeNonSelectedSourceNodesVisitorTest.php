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

namespace Infection\Tests\PhpParser\Visitor\ExcludeNonSelectedSourceNodesVisitor;

use function array_filter;
use function array_keys;
use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\PhpParser\Visitor\ExcludeNonSelectedSourceNodesVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\Source\Matcher\SourceSymbolMatcher;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeNonSelectedSourceNodesVisitor::class)]
#[CoversClass(SourceSymbolMatcher::class)]
final class ExcludeNonSelectedSourceNodesVisitorTest extends VisitorTestCase
{
    /**
     * @param list<SourceSymbolSelector> $selectors
     * @param list<int> $expectedEligibleNodeIds
     */
    #[DataProvider('scenarioProvider')]
    public function test_it_excludes_non_selected_source_nodes(
        string $code,
        array $selectors,
        array $expectedEligibleNodeIds,
    ): void {
        $nodes = $this->parse($code);

        (new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            NameResolverFactory::create(),
            new ExcludeNonSelectedSourceNodesVisitor(
                new SourceSymbolMatcher($selectors),
            ),
        ))->traverse($nodes);

        $nodesById = $this->addIdsToNodes($nodes);
        $eligibleNodeIds = array_keys(
            array_filter(
                $nodesById,
                LabelNodesAsEligibleVisitor::isEligible(...),
            ),
        );

        $this->assertSame($expectedEligibleNodeIds, $eligibleNodeIds);
    }

    public static function scenarioProvider(): iterable
    {
        $mailerClassesInDifferentNamespaces = <<<'PHP'
            <?php
            namespace App {
                class Mailer {
                    public function send(): int {
                        $value =
                            1 +
                            2;
                        return $value + 3;
                    }
                }
            }
            namespace Other {
                class Mailer {
                    public function send(): int {
                        return 4 + 5;
                    }
                }
            }
            PHP;

        yield 'qualified method excludes its namesake' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        ];

        yield 'class and absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    null,
                    6,
                ),
            ],
            [2, 4, 7, 8, 10, 11],
        ];

        yield 'method and absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    7,
                ),
            ],
            [4, 7, 8, 10, 12],
        ];

        yield 'line beyond the selected method' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    15,
                ),
            ],
            [],
        ];

        yield 'other qualified method on its absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'Other\\Mailer',
                    'send',
                    15,
                ),
            ],
            [21, 24, 25, 26, 27],
        ];

        yield 'nodes outside the selected method' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Mailer
                {
                    public function send(): int
                    {
                        return 1;
                    }

                    public function receive(): int
                    {
                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Mailer',
                    'send',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8],
        ];

        yield 'a short class name selects every matching declaration' => [
            <<<'PHP'
                <?php

                namespace App {
                    final class Differ
                    {
                        public function diff(): int
                        {
                            return 1;
                        }
                    }
                }

                namespace Vendor {
                    final class Differ
                    {
                        public function diff(): int
                        {
                            return 2;
                        }
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8, 13, 14, 15, 16, 17],
        ];

        yield 'multiple selectors are combined as a union' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Differ
                {
                    public function diff(): int
                    {
                        return 1;
                    }

                    public function diffToArray(): array
                    {
                        return [];
                    }

                    public function other(): int
                    {
                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
                new SourceSymbolSelector(
                    'App\\Differ',
                    'diffToArray',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
        ];

        yield 'an anonymous class does not inherit the enclosing symbol context' => [
            <<<'PHP'
                <?php
                namespace App;

                final class Differ
                {
                    public function diff(): int
                    {
                        $value = new class {
                            public function nested(): int
                            {
                                return 1;
                            }
                        };

                        return 2;
                    }
                }
                PHP,
            [
                new SourceSymbolSelector(
                    'Differ',
                    'diff',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8, 9, 10, 17, 18],
        ];

        $classesWithOverlappingNamespacePrefixes = <<<'PHP'
            <?php
            namespace {
                class Differ {}
            }
            namespace Differ {
                class ColorizedDiffer {}
            }
            namespace Differ\Color {
                class ColorizedDiffer {}
            }
            namespace Differential {
                class ColorizedDiffer {}
            }
            namespace Other {
                class Differ {}
            }
            namespace Other\Differ {
                class ColorizedDiffer {}
            }
            namespace App\Differ {
                class ColorizedDiffer {}
            }
            PHP;

        yield 'namespace prefix selects only its namespace and nested namespaces' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector(
                    'Differ\\',
                    null,
                    null,
                ),
            ],
            [5, 6, 9, 10],
        ];

        yield 'explicitly qualified namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector(
                    '\\Differ\\',
                    null,
                    null,
                ),
            ],
            [5, 6, 9, 10],
        ];

        yield 'nested namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector(
                    'App\\Differ\\',
                    null,
                    null,
                ),
            ],
            [25, 26],
        ];

        yield 'case insensitive namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector(
                    'dIfFeR\\',
                    null,
                    null,
                ),
            ],
            [5, 6, 9, 10],
        ];

        $globalAndNamespacedMailers = <<<'PHP'
            <?php
            namespace {
                class Mailer {}
            }
            namespace App {
                class Mailer {}
            }
            namespace Other {
                class Mailer {}
            }
            PHP;

        yield 'short name selects global and namespaced classes' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector(
                    'Mailer',
                    null,
                    null,
                ),
            ],
            [1, 2, 5, 6, 9, 10],
        ];

        yield 'explicit global name excludes namespaced classes' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector(
                    '\\Mailer',
                    null,
                    null,
                ),
            ],
            [1, 2],
        ];

        yield 'qualified name excludes other namespaces and the global class' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    null,
                    null,
                ),
            ],
            [5, 6],
        ];

        yield 'explicitly qualified class name' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector(
                    '\\App\\Mailer',
                    null,
                    null,
                ),
            ],
            [5, 6],
        ];

        yield 'case insensitive explicit global name' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector(
                    '\\mAiLeR',
                    null,
                    null,
                ),
            ],
            [1, 2],
        ];

        yield 'method declaration line precedes its statements' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    4,
                ),
            ],
            [4, 5, 6],
        ];

        yield 'first line of a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    5,
                ),
            ],
            [4, 7, 8, 9],
        ];

        yield 'line inside a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    6,
                ),
            ],
            [4, 7, 8, 10, 11],
        ];

        yield 'line after a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    8,
                ),
            ],
            [4, 13, 14, 15, 16],
        ];

        yield 'last line of the method excludes its statements' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'send',
                    9,
                ),
            ],
            [4],
        ];

        yield 'another method does not match the line alone' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'receive',
                    6,
                ),
            ],
            [],
        ];

        yield 'another qualified class does not match the method and line alone' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'Other\\Mailer',
                    'send',
                    6,
                ),
            ],
            [],
        ];

        yield 'case insensitive method name' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector(
                    'App\\Mailer',
                    'SeNd',
                    null,
                ),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        ];
    }
}
