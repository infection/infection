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

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutableFileWasProcessed;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Source\Collector\FixedSourceCollector;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use Infection\Tests\WithConsecutive;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

#[CoversClass(MutationGenerator::class)]
final class MutationGeneratorTest extends TestCase
{
    public function test_it_returns_all_the_mutations_generated_for_each_files(): void
    {
        $fileInfoA = new MockSplFileInfo('testA.txt');
        $fileInfoB = new MockSplFileInfo('testB.txt');

        $sourceCollector = new FixedSourceCollector(
            [
                $fileInfoA,
                $fileInfoB,
            ],
        );

        $mutators = ['Fake' => new IgnoreMutator(new IgnoreConfig([]), new FakeMutator())];
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $onlyCovered = true;

        $mutation0 = $this->createMock(Mutation::class);
        $mutation1 = $this->createMock(Mutation::class);
        $mutation2 = $this->createMock(Mutation::class);

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
            );

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
                [new MutableFileWasProcessed()],
                [new MutableFileWasProcessed()],
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
                new SplFileInfo('fileA'),
                new SplFileInfo('fileB'),
            ],
        );

        $mutationGenerator = new MutationGenerator(
            $sourceCollector,
            [],
            $eventDispatcherMock,
            $fileMutationGeneratorMock,
        );

        foreach ($mutationGenerator->generate(false) as $_) {
            // We just want to iterate here to trigger the generator
        }
    }
}
