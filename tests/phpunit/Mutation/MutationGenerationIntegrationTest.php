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

namespace Infection\Tests\Mutation;

use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Configuration\Configuration;
use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\Container\Container;
use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\FileStore;
use Infection\FileSystem\FileSystem;
use Infection\Mutation\Mutation;
use Infection\Source\MatcherLine\NullSourceLineMatcher;
use Infection\Source\MatcherLine\SimpleSourceLineMatcher;
use Infection\Source\MatcherLine\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Configuration\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Psr\Log\NullLogger;
use function Safe\preg_replace;
use SebastianBergmann\Diff\Differ as BaseDiffer;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use Symfony\Component\Console\Output\NullOutput;

#[Group('integration')]
#[CoversNothing]
final class MutationGenerationIntegrationTest extends TestCase
{
    /**
     * @param list<non-empty-string> $mutatorNames
     * @param list<ChangedLinesRange>|null $changedLinesRange
     * @param list<SourceSymbolSelector> $sourceSymbolSelectors
     * @param list<array{mutator: string, diff: string}> $expected
     */
    #[DataProvider('eligibleCodeMutationProvider')]
    #[DataProvider('ignoreAllMutationProvider')]
    #[DataProvider('ignoredMutatorsMutationProvider')]
    #[DataProvider('sourceSymbolSelectionMutationProvider')]
    #[DataProvider('anonymousClassSelectionMutationProvider')]
    #[DataProvider('statementLineSelectionMutationProvider')]
    #[DataProvider('methodLineSelectionMutationProvider')]
    #[DataProvider('changedLinesMutationProvider')]
    public function test_it_generates_mutations(
        string $code,
        array $mutatorNames,
        ?array $changedLinesRange,
        array $sourceSymbolSelectors,
        array $expected,
    ): void {
        $container = $this->createContainer($code, $mutatorNames, $changedLinesRange, $sourceSymbolSelectors);

        $mutations = $container
            ->getFileMutationGenerator()
            ->generate(
                sourceFile: new MockSplFileInfo(realPath: '/source.php'),
                onlyCovered: false,
                mutatorsByName: $container->getConfiguration()->mutators,
            )
        ;

        $actual = $this->createMutationDiffs($container, $mutations);

        $this->assertSame($expected, $actual);
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'FalseValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -4,3 +4,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [true, true];
                             }

                        DIFF,
                ],
                [
                    'mutator' => 'TrueValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -4,3 +4,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [false, false];
                             }

                        DIFF,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -4,3 +4,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [true];
                             }

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'FalseValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -6,3 +6,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [true, true];
                             }

                        DIFF,
                ],
                [
                    'mutator' => 'TrueValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -6,3 +6,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [false, false];
                             }

                        DIFF,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -6,3 +6,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [true];
                             }

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'FalseValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -7,3 +7,3 @@

                        -        return false;
                        +        return true;
                             }

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'TrueValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -5,3 +5,3 @@
                             public function values(): array {
                        -        return [false, true];
                        +        return [false, false];
                             }

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'FalseValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -10,3 +10,3 @@
                             public function outside(): bool {
                        -        return false;
                        +        return true;
                             }

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [
                [
                    'mutator' => 'FalseValue',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -7,3 +7,3 @@
                                     false,
                        -            false,
                        +            true,
                                 ];

                        DIFF,
                ],
                [
                    'mutator' => 'ArrayItemRemoval',
                    'diff' => <<<'DIFF'
                        --- source.php
                        +++ mutant.php
                        @@ -5,4 +5,2 @@
                                 return [
                        -            // @infection-ignore FalseValue
                        -            false,
                                     false,

                        DIFF,
                ],
            ],
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
            ['ArrayItemRemoval', 'FalseValue', 'TrueValue'],
            null,
            [],
            [],
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
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -4,3 +4,3 @@
                     public function first(): bool {
                -        return false;
                +        return true;
                     }

                DIFF,
        ];

        $secondMethodMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -8,3 +8,3 @@
                     public function second(): bool {
                -        return false;
                +        return true;
                     }

                DIFF,
        ];

        $otherClassMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -14,3 +14,3 @@
                     public function value(): bool {
                -        return false;
                +        return true;
                     }

                DIFF,
        ];

        yield 'class selection excludes mutations in other classes' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Example'),
            ],
            [
                $firstMethodMutation,
                $secondMethodMutation,
            ],
        ];

        yield 'method selection excludes mutations in other methods' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Example', 'second'),
            ],
            [$secondMethodMutation],
        ];

        yield 'class and line selection excludes boolean literals on other lines' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Example', line: 9),
            ],
            [$secondMethodMutation],
        ];

        yield 'multiple selectors include mutations matching either selector' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Example', 'first'),
                new SourceSymbolSelector('Other'),
            ],
            [
                $firstMethodMutation,
                $otherClassMutation,
            ],
        ];

        yield 'unmatched symbol selection produces no mutations' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Missing'),
            ],
            [],
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
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -8,3 +8,3 @@
                             {
                -                return false;
                +                return true;
                             }

                DIFF,
        ];

        yield 'method selection includes mutations inside its anonymous classes' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Differ', 'diff'),
            ],
            [$anonymousClassMutation],
        ];

        yield 'method and line selection includes a mutation inside an anonymous class' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Differ', 'diff', 9),
            ],
            [$anonymousClassMutation],
        ];

        yield 'method and line selection excludes anonymous class mutations on another line' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Differ', 'diff', 15),
            ],
            [],
        ];

        yield 'anonymous class methods are not methods of the enclosing named class' => [
            $code,
            ['FalseValue'],
            null,
            [
                new SourceSymbolSelector('Differ', 'value'),
            ],
            [],
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
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -4,3 +4,3 @@
                     public function values(): array {
                -        $fallback = false;
                +        $fallback = true;
                         return array_filter(

                DIFF,
        ];

        $arrayItemMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -7,3 +7,3 @@
                             [
                -                false,
                +                true,
                                 true,

                DIFF,
        ];

        $unwrapMutation = [
            'mutator' => 'UnwrapArrayFilter',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -5,8 +5,6 @@
                         $fallback = false;
                -        return array_filter(
                -            [
                -                false,
                -                true,
                -            ],
                -        );
                +        return [
                +            false,
                +            true,
                +        ];
                     }

                DIFF,
        ];

        // Lines are counted from the start of the PHP source, not from the method.
        yield 'assignment line excludes the following multiline call and its arguments' => [
            $code,
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            null,
            [
                new SourceSymbolSelector('Example', 'values', 5),
            ],
            [$assignmentMutation],
        ];

        yield 'call opening line allows unwrapping but excludes arguments on later lines' => [
            $code,
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            null,
            [
                new SourceSymbolSelector('Example', 'values', 6),
            ],
            [$unwrapMutation],
        ];

        yield 'array item line allows its literal mutation and unwrapping the enclosing call' => [
            $code,
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            null,
            [
                new SourceSymbolSelector('Example', 'values', 8),
            ],
            [
                $arrayItemMutation,
                $unwrapMutation,
            ],
        ];

        yield 'call closing line still allows unwrapping but excludes arguments on earlier lines' => [
            $code,
            ['FalseValue', 'TrueValue', 'UnwrapArrayFilter'],
            null,
            [
                new SourceSymbolSelector('Example', 'values', 11),
            ],
            [$unwrapMutation],
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
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -4,3 +4,3 @@
                     public function value(
                -        bool $fallback = false,
                +        bool $fallback = true,
                     ): bool {

                DIFF,
        ];

        $assignmentMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -6,3 +6,3 @@
                     ): bool {
                -        $value = false;
                +        $value = true;


                DIFF,
        ];

        $returnMutation = [
            'mutator' => 'TrueValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -8,3 +8,3 @@

                -        return $value || true;
                +        return $value || false;
                     }

                DIFF,
        ];

        $visibilityMutation = [
            'mutator' => 'PublicVisibility',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -3,3 +3,3 @@
                 class Example {
                -    public function value(
                +    protected function value(
                         bool $fallback = false,

                DIFF,
        ];

        yield 'whole method selection allows signature and body mutations' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value'),
            ],
            [
                $parameterMutation,
                $assignmentMutation,
                $returnMutation,
                $visibilityMutation,
            ],
        ];

        yield 'method opening line allows visibility mutation but excludes parameter and body literals' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 4),
            ],
            [$visibilityMutation],
        ];

        yield 'parameter line allows default value and method visibility mutations' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 5),
            ],
            [
                $parameterMutation,
                $visibilityMutation,
            ],
        ];

        // The method node spans its body too, so a body line currently allows
        // changing visibility on line 4. See the discussion in:
        // https://github.com/infection/infection/pull/3683#issuecomment-6705406904
        yield 'body assignment line also allows visibility mutation on the method opening line' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 7),
            ],
            [
                $assignmentMutation,
                $visibilityMutation,
            ],
        ];

        yield 'body return line also allows visibility mutation but excludes the earlier assignment' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 9),
            ],
            [
                $returnMutation,
                $visibilityMutation,
            ],
        ];

        yield 'method closing brace line still allows visibility mutation but excludes body literals' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 10),
            ],
            [$visibilityMutation],
        ];

        yield 'line after the method excludes both visibility and body mutations' => [
            $code,
            ['FalseValue', 'TrueValue', 'PublicVisibility'],
            null,
            [
                new SourceSymbolSelector('Example', 'value', 11),
            ],
            [],
        ];
    }

    public static function changedLinesMutationProvider(): iterable
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

            PHP;

        $firstMethodMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -4,3 +4,3 @@
                     public function first(): bool {
                -        return false;
                +        return true;
                     }

                DIFF,
        ];

        $secondMethodMutation = [
            'mutator' => 'FalseValue',
            'diff' => <<<'DIFF'
                --- source.php
                +++ mutant.php
                @@ -8,3 +8,3 @@
                     public function second(): bool {
                -        return false;
                +        return true;
                     }

                DIFF,
        ];

        yield 'no changed lines produces no mutations' => [
            $code,
            ['FalseValue'],
            [],
            [],
            [],
        ];

        yield 'only mutations within a changed line range are generated' => [
            $code,
            ['FalseValue'],
            [
                ChangedLinesRange::create(
                    4,
                    6,
                ),
            ],
            [],
            [$firstMethodMutation],
        ];

        yield 'mutations matching either changed line range are generated' => [
            $code,
            ['FalseValue'],
            [
                ChangedLinesRange::forLine(5),
                ChangedLinesRange::forLine(9),
            ],
            [],
            [
                $firstMethodMutation,
                $secondMethodMutation,
            ],
        ];

        yield 'symbol selection further restricts mutations on changed lines' => [
            $code,
            ['FalseValue'],
            [
                ChangedLinesRange::forLine(5),
                ChangedLinesRange::forLine(9),
            ],
            [
                new SourceSymbolSelector('Example', 'second'),
            ],
            [$secondMethodMutation],
        ];

        yield 'selected symbols outside changed lines produce no mutations' => [
            $code,
            ['FalseValue'],
            [ChangedLinesRange::forLine(5)],
            [
                new SourceSymbolSelector('Example', 'second'),
            ],
            [],
        ];
    }

    /**
     * @param list<non-empty-string> $mutatorNames
     * @param list<ChangedLinesRange>|null $changedLinesRange
     * @param list<SourceSymbolSelector> $sourceSymbolSelectors
     */
    private function createContainer(
        string $code,
        array $mutatorNames,
        ?array $changedLinesRange,
        array $sourceSymbolSelectors,
    ): Container {
        $fileSystem = $this->createStub(FileSystem::class);
        $fileSystem
            ->method('readFile')
            ->willReturn($code)
        ;

        $tracer = $this->createStub(Tracer::class);
        $tracer
            ->method('trace')
            ->willReturn($this->createTraceStub())
        ;

        $sourceLineMatcher = $changedLinesRange === null
            ? new NullSourceLineMatcher()
            : new SimpleSourceLineMatcher($changedLinesRange);

        $container = SingletonContainer::getContainer()
            ->withValues(
                logger: new NullLogger(),
                output: new NullOutput(),
                mutatorsInput: implode(
                    ',',
                    $mutatorNames,
                ),
            )
            ->cloneWithService(FileStore::class, new FileStore($fileSystem))
            ->cloneWithService(Tracer::class, $tracer)
            ->cloneWithService(SourceLineMatcher::class, $sourceLineMatcher)
        ;

        $configurationBuilder = ConfigurationBuilder::from($container->getConfiguration());

        $configuration = $configurationBuilder
            ->withSourceSymbolSelectors(...$sourceSymbolSelectors)
            ->build()
        ;

        return $container->cloneWithService(Configuration::class, $configuration);
    }

    /**
     * @param iterable<Mutation> $mutations
     *
     * @return list<array{mutator: string, diff: string}>
     */
    private function createMutationDiffs(
        Container $container,
        iterable $mutations,
    ): array {
        $codeFactory = $container->getMutantCodeFactory();
        $differ = new BaseDiffer(
            new StrictUnifiedDiffOutputBuilder(
                [
                    'fromFile' => 'source.php',
                    'toFile' => 'mutant.php',
                    'contextLines' => 1,
                ],
            ),
        );

        return take($mutations)
            ->cast(
                static fn (Mutation $mutation): array => [
                    'mutator' => $mutation->getMutatorName(),
                    // Empty context lines need no prefix; keep changed lines byte-for-byte.
                    'diff' => preg_replace(
                        '/^ $/m',
                        '',
                        $differ->diff(
                            $mutation->getOriginalFileContent(),
                            $codeFactory->createCode($mutation),
                        ),
                    ),
                ],
            )
            ->toList()
        ;
    }

    private function createTraceStub(): Trace
    {
        $trace = $this->createStub(Trace::class);
        $trace
            ->method('getAllTestsForMutation')
            ->willReturn([
                new TestLocation('someMethod', '/path/to/test.php', 0.23),
            ])
        ;

        return $trace;
    }
}
