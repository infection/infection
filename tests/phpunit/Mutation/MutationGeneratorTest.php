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

use Infection\Event\EventDispatcher;
use Infection\Event\MutableFileWasProcessed;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationGenerator;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;

final class MutationGeneratorTest extends TestCase
{
    public function test_it_returns_all_the_mutations_generated_for_each_files(): void
    {
        $sourceFiles = [
            $fileInfoA = new SplFileInfo('fileA', 'relativePathToFileA', 'relativePathnameToFileA'),
            $fileInfoB = new SplFileInfo('fileB', 'relativePathToFileB', 'relativePathnameToFileB'),
        ];

        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);
        $mutators = ['Fake' => new IgnoreMutator(new IgnoreConfig([]), new FakeMutator())];
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $onlyCovered = true;
        $extraVisitors = [2 => new FakeVisitor()];

        $mutation0 = $this->createMock(Mutation::class);
        $mutation1 = $this->createMock(Mutation::class);
        $mutation2 = $this->createMock(Mutation::class);

        /** @var FileMutationGenerator|ObjectProphecy $fileMutationGeneratorProphecy */
        $fileMutationGeneratorProphecy = $this->prophesize(FileMutationGenerator::class);
        $fileMutationGeneratorProphecy
            ->generate($fileInfoA, $onlyCovered, $codeCoverageMock, $mutators, $extraVisitors)
            ->willReturn([
                $mutation0,
                $mutation1,
            ])
        ;
        $fileMutationGeneratorProphecy
            ->generate($fileInfoB, $onlyCovered, $codeCoverageMock, $mutators, $extraVisitors)
            ->willReturn([
                $mutation1,
                $mutation2,
            ])
        ;

        $expectedMutations = [
            $mutation0,
            $mutation1,
            $mutation1,
            $mutation2,
        ];

        $mutationGenerator = new MutationGenerator(
            $sourceFiles,
            $codeCoverageMock,
            $mutators,
            $eventDispatcherMock,
            $fileMutationGeneratorProphecy->reveal()
        );

        $mutations = $mutationGenerator->generate($onlyCovered, $extraVisitors);

        $this->assertSame($expectedMutations, $mutations);
    }

    public function test_it_dispatches_events(): void
    {
        $sourceFiles = [
            new SplFileInfo('fileA', 'relativePathToFileA', 'relativePathnameToFileA'),
            new SplFileInfo('fileB', 'relativePathToFileB', 'relativePathnameToFileB'),
        ];

        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new MutationGenerationWasStarted(2)],
                [new MutableFileWasProcessed()],
                [new MutableFileWasProcessed()],
                [new MutationGenerationWasFinished()]
            )
        ;

        $fileMutationGeneratorMock = $this->createMock(FileMutationGenerator::class);
        $fileMutationGeneratorMock
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                [],
                []
            )
        ;

        $mutationGenerator = new MutationGenerator(
            $sourceFiles,
            $codeCoverageMock,
            [],
            $eventDispatcherMock,
            $fileMutationGeneratorMock
        );

        $mutationGenerator->generate(false, []);
    }
}
