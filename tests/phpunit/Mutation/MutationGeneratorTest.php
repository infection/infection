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
use Infection\TestFramework\Coverage\ProxyTrace;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\TraceProvider;
use Infection\TestFramework\Coverage\XmlReport\TestTrace;
use Infection\TestFramework\Coverage\XmlReport\TestTraceProvider;
use Infection\Tests\Fixtures\Mutator\FakeMutator;
use Infection\Tests\Fixtures\PhpParser\FakeIgnorer;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Finder\SplFileInfo;

final class MutationGeneratorTest extends TestCase
{
    public function test_it_returns_all_the_mutations_generated_for_each_files(): void
    {
        $fileInfo = $this->createMock(SplFileInfo::class);

        // Prophecy compares arguments on equality, therefore these have to be somewhat unique
        $proxyTraceA = new ProxyTrace($fileInfo, [1]);
        $proxyTraceB = new ProxyTrace($fileInfo, [2]);

        // TODO: check name and should only require to mock Trace itself
        $testTraceMock = $this->createMock(TestTrace::class);
        $mutators = ['Fake' => new IgnoreMutator(new IgnoreConfig([]), new FakeMutator())];
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $onlyCovered = true;
        $nodeIgnorers = [new FakeIgnorer()];

        $mutation0 = $this->createMock(Mutation::class);
        $mutation1 = $this->createMock(Mutation::class);
        $mutation2 = $this->createMock(Mutation::class);

        /** @var FileMutationGenerator|ObjectProphecy $fileMutationGeneratorProphecy */
        $fileMutationGeneratorProphecy = $this->prophesize(FileMutationGenerator::class);
        $fileMutationGeneratorProphecy
            ->generate($proxyTraceA, $onlyCovered, $testTraceMock, $mutators, $nodeIgnorers)
            ->shouldBeCalledTimes(1)
            ->willReturn([
                $mutation0,
                $mutation1,
            ])
        ;

        $fileMutationGeneratorProphecy
            ->generate($proxyTraceB, $onlyCovered, $testTraceMock, $mutators, $nodeIgnorers)
            ->shouldBeCalledTimes(1)
            ->willReturn([
                $mutation1,
                $mutation2,
            ])
        ;

        $providerMock = $this->createMock(TestTraceProvider::class);
        $providerMock
            ->expects($this->exactly(2))
            ->method('provideFor')
            ->withConsecutive(
                [$proxyTraceA],
                [$proxyTraceB]
             )
            ->willReturn($testTraceMock)
        ;

        $expectedMutations = [
            $mutation0,
            $mutation1,
            $mutation1,
            $mutation2,
        ];

        $fileDataProviderMock = $this->createMock(TraceProvider::class);
        $fileDataProviderMock
            ->expects($this->exactly(1))
            ->method('provideTraces')
            ->willReturn([
                $proxyTraceA,
                $proxyTraceB,
            ])
        ;

        $mutationGenerator = new MutationGenerator(
            $fileDataProviderMock,
            $providerMock,
            $mutators,
            $eventDispatcherMock,
            $fileMutationGeneratorProphecy->reveal(),
            false
        );

        $mutations = [];

        foreach ($mutationGenerator->generate($onlyCovered, $nodeIgnorers) as $mutation) {
            $mutations[] = $mutation;
        }

        $this->assertSame($expectedMutations, $mutations);
    }

    public function test_it_dispatches_events(): void
    {
        $sourceFiles = [
            new SplFileInfo('fileA', 'relativePathToFileA', 'relativePathnameToFileA'),
            new SplFileInfo('fileB', 'relativePathToFileB', 'relativePathnameToFileB'),
        ];

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

        $providerMock = $this->createMock(TestTraceProvider::class);

        $mutationGenerator = new MutationGenerator(
            $this->createSourceFileDataProviderMock($sourceFiles),
            $providerMock,
            [],
            $eventDispatcherMock,
            $fileMutationGeneratorMock,
            false
        );

        foreach ($mutationGenerator->generate(false, []) as $_) {
        }
    }

    public function test_it_does_not_count_files_in_concurrent_mode(): void
    {
        $sourceFiles = (static function () {
            yield new SplFileInfo('fileA', 'relativePathToFileA', 'relativePathnameToFileA');

            yield new SplFileInfo('fileB', 'relativePathToFileB', 'relativePathnameToFileB');
        })();

        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $eventDispatcherMock
            ->expects($this->exactly(4))
            ->method('dispatch')
            ->withConsecutive(
                [new MutationGenerationWasStarted(0)],
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

        $providerMock = $this->createMock(TestTraceProvider::class);

        $mutationGenerator = new MutationGenerator(
            $this->createSourceFileDataProviderMock($sourceFiles),
            $providerMock,
            [],
            $eventDispatcherMock,
            $fileMutationGeneratorMock,
            true
        );

        foreach ($mutationGenerator->generate(false, []) as $_) {
        }
    }

    private function createSourceFileDataProviderMock(iterable $files): TraceProvider
    {
        $providerMock = $this->createMock(TraceProvider::class);
        $providerMock
            ->method('provideTraces')
            ->willReturn(take($files)->map(static function (SplFileInfo $fileInfo) {
                return new ProxyTrace($fileInfo, [new TestLocations()]);
            }))
        ;

        return $providerMock;
    }
}
