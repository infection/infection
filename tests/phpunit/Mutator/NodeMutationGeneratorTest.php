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

namespace Infection\Tests\Mutator;

use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Configuration\Configuration;
use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\Visitor\ExcludeIgnoredMutatorsVisitor;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\Source\MatcherLine\NullSourceLineMatcher;
use Infection\Source\MatcherLine\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\PhpParser\Visitor\VisitorTestCase\VisitorTestCase;
use function iterator_to_array;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function Pipeline\take;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(NodeMutationGenerator::class)]
final class NodeMutationGeneratorTest extends VisitorTestCase
{
    public function test_it_does_not_resolve_mutator_names_for_unannotated_nodes(): void
    {
        $node = new Nop();
        LabelNodesAsEligibleVisitor::markAsEligible($node);

        $mutator = $this->createMock(Mutator::class);
        $mutator
            ->expects($this->never())
            ->method('getName')
        ;
        $mutator
            ->expects($this->once())
            ->method('canMutate')
            ->with($node)
            ->willReturn(false)
        ;
        $mutator
            ->expects($this->never())
            ->method('mutate')
        ;

        $generator = new NodeMutationGenerator(
            mutatorsByName: ['FalseValue' => $mutator],
            filePath: '/source.php',
            fileNodes: [$node],
            originalFileTokens: [],
            originalFileContent: '',
        );

        $this->assertSame(
            [],
            iterator_to_array($generator->generate($node)),
        );
    }

    /**
     * @param list<array{mutator: string, code: string}> $expectedMutations
     * @param list<non-empty-string> $mutatorNames
     * @param list<SourceSymbolSelector> $sourceSymbolSelectors
     */
    #[DataProvider('eligibleCodeMutationProvider')]
    #[DataProvider('ignoreAllMutationProvider')]
    #[DataProvider('ignoredMutatorsMutationProvider')]
    #[DataProvider('sourceSymbolSelectionMutationProvider')]
    #[DataProvider('anonymousClassSelectionMutationProvider')]
    #[DataProvider('statementLineSelectionMutationProvider')]
    #[DataProvider('methodLineSelectionMutationProvider')]
    public function test_it_generates_mutations(
        string $code,
        array $expectedMutations,
        array $mutatorNames,
        array $sourceSymbolSelectors = [],
    ): void {
        $container = SingletonContainer::getContainer()
            ->withValues(
                logger: new NullLogger(),
                output: new NullOutput(),
                mutatorsInput: implode(',', $mutatorNames),
            )
            ->cloneWithService(
                SourceLineMatcher::class,
                new NullSourceLineMatcher(),
            )
        ;

        $configuration = ConfigurationBuilder::from(
            $container->getConfiguration(),
        )
            ->withSourceSymbolSelectors(...$sourceSymbolSelectors)
            ->build()
        ;
        $container = $container->cloneWithService(
            Configuration::class,
            $configuration,
        );

        $traverserFactory = $container->getNodeTraverserFactory();
        $sourceFile = new MockSplFileInfo(realPath: '/source.php');
        $nodes = $this->parse($code);

        $traverserFactory
            ->createEnrichmentTraverser(
                $sourceFile,
                $this->createTraceStub(),
            )
            ->traverse($nodes)
        ;

        $collector = new MutationCollectorVisitor(
            new NodeMutationGenerator(
                mutatorsByName: $container->getConfiguration()->mutators,
                filePath: '/source.php',
                fileNodes: $nodes,
                originalFileTokens: $this->parser->getTokens(),
                originalFileContent: $code,
            ),
        );

        $traverserFactory
            ->createMutationTraverser($collector)
            ->traverse($nodes)
        ;

        $codeFactory = $container->getMutantCodeFactory();
        $actualMutations = take($collector->getMutations())
            ->cast(static fn (Mutation $mutation): array => [
                'mutator' => $mutation->getMutatorName(),
                'code' => $codeFactory->createCode($mutation),
            ])
            ->toList()
        ;

        $this->assertSame($expectedMutations, $actualMutations);
    }

    public static function eligibleCodeMutationProvider(): iterable
    {
        yield 'without annotations' => [
            <<<'PHP'
                <?php

                class Example {
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            [
                [
                    'mutator' => 'FalseValue',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function values(): array {
                                return [true, true];
                            }
                        }
                        PHP,
                ],
                [
                    'mutator' => 'TrueValue',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function values(): array {
                                return [false, false];
                            }
                        }
                        PHP,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function values(): array {
                                return [true];
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];

        yield 'code outside a class remains ineligible' => [
            <<<'PHP'
                <?php

                $values = [false, true];

                class Example {
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            [
                [
                    'mutator' => 'FalseValue',
                    'code' => <<<'PHP'
                        <?php

                        $values = [false, true];

                        class Example {
                            public function values(): array {
                                return [true, true];
                            }
                        }
                        PHP,
                ],
                [
                    'mutator' => 'TrueValue',
                    'code' => <<<'PHP'
                        <?php

                        $values = [false, true];

                        class Example {
                            public function values(): array {
                                return [false, false];
                            }
                        }
                        PHP,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'code' => <<<'PHP'
                        <?php

                        $values = [false, true];

                        class Example {
                            public function values(): array {
                                return [true];
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];
    }

    public static function ignoreAllMutationProvider(): iterable
    {
        yield 'ignore-all on a method excludes all descendant mutations' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore-all */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            [],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];

        yield 'ignore-all on a statement does not exclude following statements' => [
            <<<'PHP'
                <?php

                class Example {
                    public function value(): bool {
                        /** @infection-ignore-all */
                        $values = [false, true];

                        return false;
                    }
                }
                PHP,
            [
                [
                    'mutator' => 'FalseValue',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function value(): bool {
                                /** @infection-ignore-all */
                                $values = [false, true];

                                return true;
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];
    }

    public static function ignoredMutatorsMutationProvider(): iterable
    {
        yield 'multiple mutators in a docblock' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore ArrayItemRemoval, FalseValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            [
                [
                    'mutator' => 'TrueValue',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            /** @infection-ignore ArrayItemRemoval, FalseValue */
                            public function values(): array {
                                return [false, false];
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];

        yield 'ignore all still takes precedence' => [
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
            [
                [
                    'mutator' => 'FalseValue',
                    'code' => <<<'PHP'
                        <?php

                        /** @infection-ignore ArrayItemRemoval */
                        class Example {
                            public function ignored(): array {
                                // @infection-ignore-all
                                return [false, true];
                            }

                            public function outside(): bool {
                                return true;
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];

        yield 'annotation on an array item excludes its descendants only' => [
            <<<'PHP'
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
                PHP,
            [
                [
                    'mutator' => 'FalseValue',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function values(): array {
                                return [
                                    // @infection-ignore FalseValue
                                    false,
                                    true,
                                ];
                            }
                        }
                        PHP,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'code' => <<<'PHP'
                        <?php

                        class Example {
                            public function values(): array {
                                return [
                                    false,
                                ];
                            }
                        }
                        PHP,
                ],
            ],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];

        yield 'all selected mutators can be excluded' => [
            <<<'PHP'
                <?php

                class Example {
                    /** @infection-ignore ArrayItemRemoval, FalseValue, TrueValue */
                    public function values(): array {
                        return [false, true];
                    }
                }
                PHP,
            [],
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
        ];
    }

    public static function sourceSymbolSelectionMutationProvider(): iterable
    {
        $code = <<<'PHP'
            <?php

            class Example {
                public function first(): bool {
                    return false;
                }

                public function second(): bool {
                    return false;
                }
            }

            class Other {
                public function value(): bool {
                    return false;
                }
            }
            PHP;

        $firstMethodMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function first(): bool {
                        return true;
                    }

                    public function second(): bool {
                        return false;
                    }
                }

                class Other {
                    public function value(): bool {
                        return false;
                    }
                }
                PHP,
        ];

        $secondMethodMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function first(): bool {
                        return false;
                    }

                    public function second(): bool {
                        return true;
                    }
                }

                class Other {
                    public function value(): bool {
                        return false;
                    }
                }
                PHP,
        ];

        $otherClassMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function first(): bool {
                        return false;
                    }

                    public function second(): bool {
                        return false;
                    }
                }

                class Other {
                    public function value(): bool {
                        return true;
                    }
                }
                PHP,
        ];

        yield 'class selection excludes mutations in other classes' => [
            $code,
            [$firstMethodMutation, $secondMethodMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: null,
                    line: null,
                ),
            ],
        ];

        yield 'method selection excludes mutations in other methods' => [
            $code,
            [$secondMethodMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'second',
                    line: null,
                ),
            ],
        ];

        yield 'class and line selection excludes boolean literals on other lines' => [
            $code,
            [$secondMethodMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: null,
                    line: 9,
                ),
            ],
        ];

        yield 'multiple selectors include mutations matching either selector' => [
            $code,
            [$firstMethodMutation, $otherClassMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'first',
                    line: null,
                ),
                new SourceSymbolSelector(
                    className: 'Other',
                    methodName: null,
                    line: null,
                ),
            ],
        ];

        yield 'unmatched symbol selection produces no mutations' => [
            $code,
            [],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Missing',
                    methodName: null,
                    line: null,
                ),
            ],
        ];
    }

    public static function anonymousClassSelectionMutationProvider(): iterable
    {
        $code = <<<'PHP'
            <?php
            class Differ
            {
                public function diff(): object
                {
                    return new class {
                        public function value(): bool
                        {
                            return false;
                        }
                    };
                }

                public function other(): bool
                {
                    return false;
                }
            }
            PHP;

        $anonymousClassMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php
                class Differ
                {
                    public function diff(): object
                    {
                        return new class {
                            public function value(): bool
                            {
                                return true;
                            }
                        };
                    }

                    public function other(): bool
                    {
                        return false;
                    }
                }
                PHP,
        ];

        yield 'method selection includes mutations inside its anonymous classes' => [
            $code,
            [$anonymousClassMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Differ',
                    methodName: 'diff',
                    line: null,
                ),
            ],
        ];

        yield 'method and line selection includes a mutation inside an anonymous class' => [
            $code,
            [$anonymousClassMutation],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Differ',
                    methodName: 'diff',
                    line: 9,
                ),
            ],
        ];

        yield 'method and line selection excludes anonymous class mutations on another line' => [
            $code,
            [],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Differ',
                    methodName: 'diff',
                    line: 15,
                ),
            ],
        ];

        yield 'anonymous class methods are not methods of the enclosing named class' => [
            $code,
            [],
            ['FalseValue'],
            [
                new SourceSymbolSelector(
                    className: 'Differ',
                    methodName: 'value',
                    line: null,
                ),
            ],
        ];
    }

    public static function statementLineSelectionMutationProvider(): iterable
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

        $assignmentMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function values(): array {
                        $fallback = true;
                        return array_filter(
                            [
                                false,
                                true,
                            ],
                        );
                    }
                }
                PHP,
        ];

        $arrayItemMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function values(): array {
                        $fallback = false;
                        return array_filter(
                            [
                                true,
                                true,
                            ],
                        );
                    }
                }
                PHP,
        ];

        $unwrapMutation = [
            'mutator' => 'UnwrapArrayFilter',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function values(): array {
                        $fallback = false;
                        return [
                            false,
                            true,
                        ];
                    }
                }
                PHP,
        ];

        // Lines are counted from the start of the PHP source, not from the method.
        yield 'assignment line excludes the following multiline call and its arguments' => [
            $code,
            [$assignmentMutation],
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'values',
                    line: 5,
                ),
            ],
        ];

        yield 'call opening line allows unwrapping but excludes arguments on later lines' => [
            $code,
            [$unwrapMutation],
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'values',
                    line: 6,
                ),
            ],
        ];

        yield 'array item line allows its literal mutation and unwrapping the enclosing call' => [
            $code,
            [$arrayItemMutation, $unwrapMutation],
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'values',
                    line: 8,
                ),
            ],
        ];

        yield 'call closing line still allows unwrapping but excludes arguments on earlier lines' => [
            $code,
            [$unwrapMutation],
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'values',
                    line: 11,
                ),
            ],
        ];
    }

    public static function methodLineSelectionMutationProvider(): iterable
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

        $parameterMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function value(
                        bool $fallback = true,
                    ): bool {
                        $value = false;

                        return $value || true;
                    }
                }
                PHP,
        ];

        $assignmentMutation = [
            'mutator' => 'FalseValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function value(
                        bool $fallback = false,
                    ): bool {
                        $value = true;

                        return $value || true;
                    }
                }
                PHP,
        ];

        $returnMutation = [
            'mutator' => 'TrueValue',
            'code' => <<<'PHP'
                <?php

                class Example {
                    public function value(
                        bool $fallback = false,
                    ): bool {
                        $value = false;

                        return $value || false;
                    }
                }
                PHP,
        ];

        $visibilityMutation = [
            'mutator' => 'PublicVisibility',
            'code' => <<<'PHP'
                <?php

                class Example {
                    protected function value(
                        bool $fallback = false,
                    ): bool {
                        $value = false;

                        return $value || true;
                    }
                }
                PHP,
        ];

        yield 'whole method selection allows signature and body mutations' => [
            $code,
            [$parameterMutation, $assignmentMutation, $returnMutation, $visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: null,
                ),
            ],
        ];

        yield 'method opening line allows visibility mutation but excludes parameter and body literals' => [
            $code,
            [$visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 4,
                ),
            ],
        ];

        yield 'parameter line allows default value and method visibility mutations' => [
            $code,
            [$parameterMutation, $visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 5,
                ),
            ],
        ];

        // The method node spans its body too, so a body line currently allows
        // changing visibility on line 4. See the discussion in:
        // https://github.com/infection/infection/pull/3683#issuecomment-6705406904
        yield 'body assignment line also allows visibility mutation on the method opening line' => [
            $code,
            [$assignmentMutation, $visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 7,
                ),
            ],
        ];

        yield 'body return line also allows visibility mutation but excludes the earlier assignment' => [
            $code,
            [$returnMutation, $visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 9,
                ),
            ],
        ];

        yield 'method closing brace line still allows visibility mutation but excludes body literals' => [
            $code,
            [$visibilityMutation],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 10,
                ),
            ],
        ];

        yield 'line after the method excludes both visibility and body mutations' => [
            $code,
            [],
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            [
                new SourceSymbolSelector(
                    className: 'Example',
                    methodName: 'value',
                    line: 11,
                ),
            ],
        ];
    }

    #[DataProvider('ignoredMutatorNameProvider')]
    public function test_it_does_not_consult_ignored_mutators(string $mutatorName): void
    {
        $node = new Nop();
        LabelNodesAsEligibleVisitor::markAsEligible($node);
        $node->setAttribute(
            ExcludeIgnoredMutatorsVisitor::IGNORED_MUTATORS,
            [$mutatorName],
        );

        $mutator = $this->createMock(Mutator::class);
        $mutator
            ->expects($this->never())
            ->method('getName')
        ;
        $mutator
            ->expects($this->never())
            ->method('canMutate')
        ;
        $mutator
            ->expects($this->never())
            ->method('mutate')
        ;

        $generator = new NodeMutationGenerator(
            mutatorsByName: [$mutatorName => $mutator],
            filePath: '/source.php',
            fileNodes: [$node],
            originalFileTokens: [],
            originalFileContent: '',
        );

        $this->assertSame(
            [],
            iterator_to_array($generator->generate($node)),
        );
    }

    public static function ignoredMutatorNameProvider(): iterable
    {
        yield 'built-in mutator' => ['FalseValue'];

        yield 'custom mutator' => ['Acme\\CustomMutator'];
    }

    private function createTraceStub(): Trace
    {
        $trace = $this->createStub(Trace::class);
        $trace
            ->method('getAllTestsForMutation')
            ->willReturn([
                new TestLocation(
                    'someMethod',
                    '/path/to/test.php',
                    0.23,
                ),
            ])
        ;

        return $trace;
    }
}
