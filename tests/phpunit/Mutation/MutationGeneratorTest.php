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

use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Source\Collector\FixedSourceCollector;
use Infection\Source\Exception\SourceSymbolNotFound;
use Infection\Source\Matcher\SourceSymbolMatcher;
use Infection\Testing\FileSystem\MockSplFileInfo;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use Infection\Tests\WithConsecutive;
use function iterator_to_array;
use PhpParser\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationGenerator::class)]
final class MutationGeneratorTest extends TestCase
{
    public function test_it_returns_all_the_mutations_generated_for_each_files(): void
    {
        $fileInfoA = new MockSplFileInfo(realPath: '/path/to/fileA.txt');
        $fileInfoB = new MockSplFileInfo(realPath: '/path/to/fileB.txt');

        $sourceCollector = new FixedSourceCollector(
            [
                $fileInfoA,
                $fileInfoB,
            ],
        );

        $mutators = ['Fake' => new IgnoreMutator(new IgnoreConfig([]), new FakeMutator())];
        $eventDispatcherMock = $this->createStub(EventDispatcher::class);
        $onlyCovered = true;

        $mutation0 = $this->createStub(Mutation::class);
        $mutation1 = $this->createStub(Mutation::class);
        $mutation2 = $this->createStub(Mutation::class);

        $fileMutationGenerator = $this->createMock(FileMutationGenerator::class);
        $fileMutationGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->with(
                ...WithConsecutive::create(
                    [$fileInfoA, $onlyCovered, $mutators],
                    [$fileInfoB, $onlyCovered, $mutators],
                ),
            )
            ->willReturnOnConsecutiveCalls(
                [$mutation0, $mutation1],
                [$mutation1, $mutation2],
            )
        ;

        $expectedMutations = [
            $mutation0,
            $mutation1,
            $mutation1,
            $mutation2,
        ];

        $mutationGenerator = new MutationGenerator(
            $sourceCollector,
            $mutators,
            $eventDispatcherMock,
            $fileMutationGenerator,
            new SourceSymbolMatcher([]),
        );

        $mutations = iterator_to_array(
            $mutationGenerator->generate($onlyCovered),
            preserve_keys: false,
        );

        $this->assertSame($expectedMutations, $mutations);
    }

    public function test_it_dispatches_events(): void
    {
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->with(...WithConsecutive::create(
                [new MutationGenerationWasStarted(2)],
                [new MutableFileWasProcessed(
                    'path/to/fileA',
                    [],
                )],
                [new MutableFileWasProcessed(
                    'path/to/fileB',
                    [],
                )],
                [new MutationGenerationWasFinished()],
            ))
        ;

        $fileMutationGeneratorMock = $this->createMock(FileMutationGenerator::class);
        $fileMutationGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')->willReturn([])
        ;

        $sourceCollector = new FixedSourceCollector(
            [
                new MockSplFileInfo(realPath: 'path/to/fileA'),
                new MockSplFileInfo(realPath: 'path/to/fileB'),
            ],
        );

        $mutationGenerator = new MutationGenerator(
            $sourceCollector,
            [],
            $eventDispatcherMock,
            $fileMutationGeneratorMock,
            new SourceSymbolMatcher([]),
        );

        foreach ($mutationGenerator->generate(false) as $_) {
            // We just want to iterate here to trigger the generator
        }
    }

    public function test_it_rejects_unmatched_source_selectors_after_processing_every_source(): void
    {
        $selector = new SourceSymbolSelector('Differ', 'missing', null);
        $sourceSymbolMatcher = new SourceSymbolMatcher([$selector]);
        $method = new Node\Stmt\ClassMethod('missing');
        $class = new Node\Stmt\Class_('Differ', [
            'stmts' => [$method],
        ]);
        $class->namespacedName = new Node\Name('Differ');
        $this->assertTrue($sourceSymbolMatcher->matches($method, $class, $method));
        $fileMutationGenerator = $this->createMock(FileMutationGenerator::class);
        $fileMutationGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn([])
        ;
        $mutationGenerator = new MutationGenerator(
            new FixedSourceCollector([
                new MockSplFileInfo(realPath: 'path/to/fileA'),
                new MockSplFileInfo(realPath: 'path/to/fileB'),
            ]),
            [],
            $this->createStub(EventDispatcher::class),
            $fileMutationGenerator,
            $sourceSymbolMatcher,
        );

        $this->expectException(SourceSymbolNotFound::class);
        $this->expectExceptionMessage('The following source selectors did not match any source symbol: "Differ::missing".');

        iterator_to_array($mutationGenerator->generate(false));
    }

    public function test_it_rejects_each_unmatched_source_selector(): void
    {
        $sourceSymbolMatcher = new SourceSymbolMatcher([
            new SourceSymbolSelector('Differ', 'diff', null),
            new SourceSymbolSelector('Differ', 'missing', null),
        ]);
        $method = new Node\Stmt\ClassMethod('diff');
        $class = new Node\Stmt\Class_('Differ', [
            'stmts' => [$method],
        ]);
        $class->namespacedName = new Node\Name('Differ');
        $fileMutationGenerator = $this->createStub(FileMutationGenerator::class);
        $fileMutationGenerator
            ->method('generate')
            ->willReturnCallback(
                static function () use ($sourceSymbolMatcher, $method, $class): array {
                    $sourceSymbolMatcher->matches($method, $class, $method);

                    return [];
                },
            )
        ;
        $mutationGenerator = new MutationGenerator(
            new FixedSourceCollector([
                new MockSplFileInfo(realPath: 'path/to/file'),
            ]),
            [],
            $this->createStub(EventDispatcher::class),
            $fileMutationGenerator,
            $sourceSymbolMatcher,
        );

        $this->expectException(SourceSymbolNotFound::class);
        $this->expectExceptionMessage('The following source selectors did not match any source symbol: "Differ::missing".');

        iterator_to_array($mutationGenerator->generate(false));
    }
}
