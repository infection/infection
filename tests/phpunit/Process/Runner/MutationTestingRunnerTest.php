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

namespace Infection\Tests\Process\Runner;

use function count;
use function get_class;
use Infection\Event\MutationTestingFinished;
use Infection\Event\MutationTestingStarted;
use Infection\Mutation\Mutation;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\MutantProcessFactory;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MutationTestingRunnerTest extends TestCase
{
    /**
     * @var MutantProcessFactory|MockObject
     */
    private $mutantProcessFactoryMock;

    /**
     * @var ParallelProcessRunner|MockObject
     */
    private $parallelProcessRunnerMock;

    /**
     * @var \Infection\Tests\Fixtures\Event\EventDispatcherCollector
     */
    private $eventDispatcher;

    /**
     * @var MutationTestingRunner
     */
    private $runner;

    protected function setUp(): void
    {
        $this->mutantProcessFactoryMock = $this->createMock(MutantProcessFactory::class);
        $this->parallelProcessRunnerMock = $this->createMock(ParallelProcessRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();

        $this->runner = new MutationTestingRunner(
            $this->mutantProcessFactoryMock,
            $this->parallelProcessRunnerMock,
            $this->eventDispatcher
        );
    }

    public function test_it_applies_and_run_the_mutations(): void
    {
        $mutations = [
            $this->createMock(Mutation::class),
            $this->createMock(Mutation::class),
        ];
        $threadCount = 4;
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantProcessFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($mutations, $testFrameworkExtraOptions)
            ->willReturn($processes = [
                $this->createMock(MutantProcess::class),
                $this->createMock(MutantProcess::class),
            ])
        ;

        $this->parallelProcessRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($processes, $threadCount)
        ;

        $this->runner->run($mutations, $threadCount, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingStarted(2),
                new MutationTestingFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    public function test_it_dispatches_events_even_when_no_mutations_is_given(): void
    {
        $mutations = [];
        $threadCount = 4;
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantProcessFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($mutations, $testFrameworkExtraOptions)
            ->willReturn($processes = [])
        ;

        $this->parallelProcessRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($processes, $threadCount)
        ;

        $this->runner->run($mutations, $threadCount, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingStarted(0),
                new MutationTestingFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    /**
     * @param array<MutationTestingStarted|MutationTestingFinished> $expectedEvents
     * @param array<MutationTestingStarted|MutationTestingFinished> $actualEvents
     */
    private function assertAreSameEvents(array $expectedEvents, array $actualEvents): void
    {
        foreach ($expectedEvents as $index => $expectedEvent) {
            $this->assertArrayHasKey($index, $actualEvents);

            $event = $actualEvents[$index];

            $this->assertInstanceOf(get_class($expectedEvent), $event);

            if ($expectedEvent instanceof MutationTestingStarted) {
                $this->assertSame($expectedEvent->getMutationCount(), $event->getMutationCount());
            }
        }

        $this->assertCount(count($expectedEvents), $actualEvents);
    }
}
