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
use Infection\Events\MutantCreated;
use Infection\Events\MutantsCreatingFinished;
use Infection\Events\MutantsCreatingStarted;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\MutantProcessFactory;
use Infection\Tests\Fixtures\Process\EventDispatcherCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class MutantProcessFactoryTest extends TestCase
{
    /**
     * @var MutantProcessBuilder|MockObject
     */
    private $processBuilderMock;

    /**
     * @var MutantFactory|MockObject
     */
    private $mutantFactoryMock;

    /**
     * @var EventDispatcherCollector
     */
    private $eventDispatcher;

    /**
     * @var MutantProcessFactory
     */
    private $processFactory;

    protected function setUp(): void
    {
        $this->processBuilderMock = $this->createMock(MutantProcessBuilder::class);
        $this->mutantFactoryMock = $this->createMock(MutantFactory::class);
        $this->eventDispatcher = new EventDispatcherCollector();

        $this->processFactory = new MutantProcessFactory(
            $this->processBuilderMock,
            $this->mutantFactoryMock,
            $this->eventDispatcher
        );
    }

    public function test_it_does_not_create_processes_when_there_is_not_mutations(): void
    {
        $processes = $this->processFactory->create([], '');

        $this->assertSame([], $processes);

        $this->assertAreSameEvents(
            [
                new MutantsCreatingStarted(0),
                new MutantsCreatingFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    public function test_it_creates_a_process_for_each_mutation(): void
    {
        $mutations = [
            $mutation0 = $this->createMock(Mutation::class),
            $mutation1 = $this->createMock(Mutation::class),
        ];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantFactoryMock
            ->method('create')
            ->withConsecutive(
                [$mutation0],
                [$mutation1]
            )
            ->willReturnOnConsecutiveCalls(
                $mutant0 = new Mutant('/path/to/mutant0', $mutation0, ''),
                $mutant1 = new Mutant('/path/to/mutant1', $mutation1, '')
            )
        ;

        $this->processBuilderMock
            ->method('createProcessForMutant')
            ->withConsecutive(
                [$mutant0, $testFrameworkExtraOptions],
                [$mutant1, $testFrameworkExtraOptions]
            )
            ->willReturnOnConsecutiveCalls(
                $process0 = $this->createMock(MutantProcess::class),
                $process1 = $this->createMock(MutantProcess::class)
            )
        ;

        $processes = $this->processFactory->create($mutations, $testFrameworkExtraOptions);

        $this->assertSame(
            [
                $process0,
                $process1,
            ],
            $processes
        );

        $this->assertAreSameEvents(
            [
                new MutantsCreatingStarted(2),
                new MutantCreated(),
                new MutantCreated(),
                new MutantsCreatingFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    /**
     * @param array<MutantsCreatingStarted|MutantCreated|MutantsCreatingFinished> $expectedEvents
     * @param array<MutantsCreatingStarted|MutantCreated|MutantsCreatingFinished> $actualEvents
     */
    private function assertAreSameEvents(array $expectedEvents, array $actualEvents): void
    {
        foreach ($expectedEvents as $index => $expectedEvent) {
            $this->assertArrayHasKey($index, $actualEvents);

            $event = $actualEvents[$index];

            $this->assertInstanceOf(get_class($expectedEvent), $event);

            if ($expectedEvent instanceof MutantsCreatingStarted) {
                $this->assertSame($expectedEvent->getMutantCount(), $event->getMutantCount());
            }
        }

        $this->assertCount(count($expectedEvents), $actualEvents);
    }
}
