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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\Source\MatcherLine\NullSourceLineMatcher;
use Infection\Source\MatcherLine\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Testing\SingletonContainer;
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
     */
    #[DataProvider('mutationProvider')]
    public function test_it_generates_mutations(
        string $code,
        array $expectedMutations,
    ): void {
        $container = SingletonContainer::getContainer()
            ->withValues(
                logger: new NullLogger(),
                output: new NullOutput(),
                mutatorsInput: 'ArrayItemRemoval,FalseValue,TrueValue',
            )
            ->cloneWithService(
                SourceLineMatcher::class,
                new NullSourceLineMatcher(),
            )
        ;
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

    public static function mutationProvider(): iterable
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
        ];
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
