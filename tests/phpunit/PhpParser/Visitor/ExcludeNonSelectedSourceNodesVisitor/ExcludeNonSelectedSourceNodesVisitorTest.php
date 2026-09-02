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
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ExcludeNonSelectedSourceNodesVisitor::class)]
#[CoversClass(SourceSymbolMatcher::class)]
final class ExcludeNonSelectedSourceNodesVisitorTest extends VisitorTestCase
{
    public function test_it_applies_method_selection_to_lexically_nested_function_like_nodes(): void
    {
        $nodes = $this->parse(<<<'PHP'
            <?php

            function outside(): int
            {
                return 0;
            }

            final class Example
            {
                public function selected(): void
                {
                    $closure = static function (): int {
                        return 1;
                    };
                    $arrow = static fn (): int => 2;

                    function nestedInSelected(): int
                    {
                        return 3;
                    }
                }

                public function unselected(): void
                {
                    $closure = static function (): int {
                        return 4;
                    };
                    $arrow = static fn (): int => 5;

                    function nestedInUnselected(): int
                    {
                        return 6;
                    }
                }
            }
            PHP);

        (new NodeTraverser(
            new LabelNodesAsEligibleVisitor(),
            NameResolverFactory::create(),
            new ExcludeNonSelectedSourceNodesVisitor(
                new SourceSymbolMatcher([
                    new SourceSymbolSelector(
                        'Example',
                        'selected',
                        null,
                    ),
                ]),
            ),
        ))->traverse($nodes);

        $functionLikes = (new NodeFinder())->findInstanceOf(
            $nodes,
            Node\FunctionLike::class,
        );

        $this->assertFalse(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[0]),
            'top-level named function',
        );
        $this->assertTrue(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[1]),
            'selected method',
        );
        $this->assertTrue(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[2]),
            'closure in selected method',
        );
        $this->assertTrue(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[3]),
            'arrow function in selected method',
        );
        $this->assertTrue(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[4]),
            'named function in selected method',
        );
        $this->assertFalse(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[5]),
            'unselected method',
        );
        $this->assertFalse(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[6]),
            'closure in unselected method',
        );
        $this->assertFalse(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[7]),
            'arrow function in unselected method',
        );
        $this->assertFalse(
            LabelNodesAsEligibleVisitor::isEligible($functionLikes[8]),
            'named function in unselected method',
        );
    }

    /**
     * @param list<SourceSymbolSelector> $selectors
     * @param list<int> $expectedEligibleNodeIds
     */
    #[DataProvider('scenarioProvider')]
    #[DataProvider('statementLineSelectionProvider')]
    #[DataProvider('methodLineSelectionProvider')]
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

        yield 'qualified method selection excludes a method with the same name in another namespace' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send'),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        ];

        yield 'an unmatched class excludes every node' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('Missing'),
            ],
            [],
        ];

        yield 'class and absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', line: 6),
            ],
            [2, 4, 7, 8, 10, 11],
        ];

        yield 'method and absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 7),
            ],
            [4, 7, 8, 10, 12],
        ];

        yield 'line beyond the selected method' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 15),
            ],
            [],
        ];

        yield 'other qualified method on its absolute line' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('Other\\Mailer', 'send', 15),
            ],
            [21, 24, 25, 26, 27],
        ];

        $mailerWithTwoMethods = <<<'PHP'
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
            PHP;

        yield 'nodes outside the selected method' => [
            $mailerWithTwoMethods,
            [
                new SourceSymbolSelector('Mailer', 'send'),
            ],
            [4, 5, 6, 7, 8],
        ];

        yield 'class selection includes every method in the class' => [
            $mailerWithTwoMethods,
            [
                new SourceSymbolSelector('Mailer'),
            ],
            [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
        ];

        yield 'a method selector and a class selector in another namespace form a union' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send'),
                new SourceSymbolSelector('Other\\Mailer'),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 24, 25, 26, 27],
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
                new SourceSymbolSelector('Differ', 'diff'),
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
                new SourceSymbolSelector('Differ', 'diff'),
                new SourceSymbolSelector('App\\Differ', 'diffToArray'),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
        ];

        $methodContainingAnonymousClass = <<<'PHP'
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
            PHP;

        yield 'an anonymous class inherits the enclosing symbol context' => [
            $methodContainingAnonymousClass,
            [
                new SourceSymbolSelector('Differ', 'diff'),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
        ];

        yield 'class selection includes anonymous classes in its methods' => [
            $methodContainingAnonymousClass,
            [
                new SourceSymbolSelector('Differ'),
            ],
            [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
        ];

        yield 'method and line selection reaches inside an anonymous class' => [
            $methodContainingAnonymousClass,
            [
                new SourceSymbolSelector('Differ', 'diff', 11),
            ],
            [4, 7, 8, 10, 11, 12, 15, 16],
        ];

        yield 'line after an anonymous class excludes its nodes while keeping the enclosing method' => [
            $methodContainingAnonymousClass,
            [
                new SourceSymbolSelector('Differ', 'diff', 15),
            ],
            [4, 17, 18],
        ];

        yield 'an anonymous class method cannot be selected as a method of its enclosing class' => [
            $methodContainingAnonymousClass,
            [
                new SourceSymbolSelector('Differ', 'nested'),
            ],
            [],
        ];

        yield 'nested anonymous classes inherit the enclosing named method' => [
            <<<'PHP'
                <?php
                class Differ
                {
                    public function diff()
                    {
                        return new class {
                            public function nested()
                            {
                                return new class {
                                    public function value()
                                    {
                                        return 1;
                                    }
                                };
                            }
                        };
                    }
                }
                PHP,
            [
                new SourceSymbolSelector('Differ', 'diff'),
            ],
            [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
        ];

        yield 'a named class inside an anonymous class has its own symbol context' => [
            <<<'PHP'
                <?php
                class Differ
                {
                    public function diff()
                    {
                        return new class {
                            public function nested()
                            {
                                class Other
                                {
                                    public function value()
                                    {
                                        return 1;
                                    }
                                }

                                return 2;
                            }
                        };
                    }
                }
                PHP,
            [
                new SourceSymbolSelector('Differ', 'diff'),
            ],
            [2, 3, 4, 5, 6, 7, 8, 15, 16],
        ];

        yield 'an anonymous class without an enclosing named class does not match a method selector' => [
            <<<'PHP'
                <?php
                $value = new class {
                    public function diff()
                    {
                        return 1;
                    }
                };
                PHP,
            [
                new SourceSymbolSelector('Differ', 'diff'),
            ],
            [],
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
                new SourceSymbolSelector('Differ\\'),
            ],
            [5, 6, 9, 10],
        ];

        yield 'explicitly qualified namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector('\\Differ\\'),
            ],
            [5, 6, 9, 10],
        ];

        yield 'nested namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector('App\\Differ\\'),
            ],
            [25, 26],
        ];

        yield 'case insensitive namespace prefix' => [
            $classesWithOverlappingNamespacePrefixes,
            [
                new SourceSymbolSelector('dIfFeR\\'),
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
                new SourceSymbolSelector('Mailer'),
            ],
            [1, 2, 5, 6, 9, 10],
        ];

        yield 'explicit global name excludes namespaced classes' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector('\\Mailer'),
            ],
            [1, 2],
        ];

        yield 'qualified name excludes other namespaces and the global class' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector('App\\Mailer'),
            ],
            [5, 6],
        ];

        yield 'explicitly qualified class name' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector('\\App\\Mailer'),
            ],
            [5, 6],
        ];

        yield 'case insensitive explicit global name' => [
            $globalAndNamespacedMailers,
            [
                new SourceSymbolSelector('\\mAiLeR'),
            ],
            [1, 2],
        ];

        yield 'method declaration line precedes its statements' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 4),
            ],
            [4, 5, 6],
        ];

        yield 'first line of a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 5),
            ],
            [4, 7, 8, 9],
        ];

        yield 'line inside a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 6),
            ],
            [4, 7, 8, 10, 11],
        ];

        yield 'line after a multiline statement' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 8),
            ],
            [4, 13, 14, 15, 16],
        ];

        yield 'last line of the method excludes its statements' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'send', 9),
            ],
            [4],
        ];

        yield 'another method does not match the line alone' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'receive', 6),
            ],
            [],
        ];

        yield 'another qualified class does not match the method and line alone' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('Other\\Mailer', 'send', 6),
            ],
            [],
        ];

        yield 'case insensitive method name' => [
            $mailerClassesInDifferentNamespaces,
            [
                new SourceSymbolSelector('App\\Mailer', 'SeNd'),
            ],
            [4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
        ];
    }

    public static function statementLineSelectionProvider(): iterable
    {
        $code = <<<'PHP'
            <?php

            class Example {
                public function values(): array {
                    $fallback = false;
                    return array_filter(
                        [
                            false,
                            true,
                        ],
                    );
                }
            }
            PHP;

        // Node 2 is the method, 5-9 are the assignment, 10 is the return,
        // 11 is the call, 12 is its name, and 13-20 are its array argument.
        yield 'assignment line excludes the following multiline call and its arguments' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'values', 5),
            ],
            [2, 5, 6, 7, 8, 9],
        ];

        yield 'call opening line keeps the call but excludes its arguments on later lines' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'values', 6),
            ],
            [2, 10, 11, 12],
        ];

        yield 'array item line keeps the item and enclosing call but excludes the other item' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'values', 8),
            ],
            [2, 10, 11, 13, 14, 15, 16, 17],
        ];

        yield 'call closing line keeps the call but excludes its arguments on earlier lines' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'values', 11),
            ],
            [2, 10, 11],
        ];
    }

    public static function methodLineSelectionProvider(): iterable
    {
        $code = <<<'PHP'
            <?php

            class Example {
                public function value(
                    bool $fallback = false,
                ): bool {
                    $value = false;

                    return $value || true;
                }
            }
            PHP;

        // Node 2 is the method, 3 its name, 4-8 its parameter and default,
        // 9 its return type, 10-14 the assignment, and 15-19 the return statement.
        yield 'whole method selection includes its signature and body' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value'),
            ],
            [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19],
        ];

        yield 'method opening line excludes parameter and body nodes on later lines' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 4),
            ],
            [2, 3],
        ];

        yield 'parameter line keeps its default and the enclosing method' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 5),
            ],
            [2, 4, 5, 6, 7, 8],
        ];

        yield 'body assignment line keeps the method declaration but excludes its other statements' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 7),
            ],
            [2, 10, 11, 12, 13, 14],
        ];

        yield 'body return line keeps the method declaration but excludes the earlier assignment' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 9),
            ],
            [2, 15, 16, 17, 18, 19],
        ];

        yield 'method closing brace line keeps only the method declaration' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 10),
            ],
            [2],
        ];

        yield 'line after the method excludes its declaration and body' => [
            $code,
            [
                new SourceSymbolSelector('Example', 'value', 11),
            ],
            [],
        ];
    }
}
