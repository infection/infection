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

use function array_map;
use function count;
use function get_class;
use function implode;
use Infection\Event\MutantsCreationWasFinished;
use Infection\Event\MutantsCreationWasStarted;
use Infection\Event\MutantWasCreated;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\Mutant;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Process\Builder\MutantProcessBuilder;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\Parallel\ParallelProcessRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;

final class MutationTestingRunnerTest extends TestCase
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
     * @var ParallelProcessRunner|MockObject
     */
    private $parallelProcessRunnerMock;

    /**
     * @var EventDispatcherCollector
     */
    private $eventDispatcher;

    /**
     * @var MutationTestingRunner
     */
    private $runner;

    protected function setUp(): void
    {
        $this->processBuilderMock = $this->createMock(MutantProcessBuilder::class);
        $this->mutantFactoryMock = $this->createMock(MutantFactory::class);
        $this->parallelProcessRunnerMock = $this->createMock(ParallelProcessRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();

        $this->runner = new MutationTestingRunner(
            $this->processBuilderMock,
            $this->mutantFactoryMock,
            $this->parallelProcessRunnerMock,
            $this->eventDispatcher
        );
    }

    public function test_it_does_not_create_processes_when_there_is_not_mutations(): void
    {
        $mutations = [];
        $threadCount = 4;
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->parallelProcessRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with([], $threadCount)
        ;

        $this->runner->run($mutations, $threadCount, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutantsCreationWasStarted(0),
                new MutantsCreationWasFinished(),
                new MutationTestingWasStarted(0),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    public function test_it_applies_and_run_the_mutations(): void
    {
        $mutations = [
            $mutation0 = $this->createMock(Mutation::class),
            $mutation1 = $this->createMock(Mutation::class),
        ];
        $threadCount = 4;
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

        $this->parallelProcessRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with([$process0, $process1], $threadCount)
        ;

        $this->runner->run($mutations, $threadCount, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutantsCreationWasStarted(2),
                new MutantWasCreated(),
                new MutantWasCreated(),
                new MutantsCreationWasFinished(),
                new MutationTestingWasStarted(2),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    public function test_it_dispatches_events_even_when_no_mutations_is_given(): void
    {
        $mutations = [];
        $threadCount = 4;
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->processBuilderMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->mutantFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->parallelProcessRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with([], $threadCount)
        ;

        $this->runner->run($mutations, $threadCount, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutantsCreationWasStarted(0),
                new MutantsCreationWasFinished(),
                new MutationTestingWasStarted(0),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents()
        );
    }

    /**
     * @param array<MutationTestingWasStarted|MutationTestingWasFinished|MutantsCreationWasStarted|MutantWasCreated|MutantsCreationWasFinished> $expectedEvents
     * @param array<MutationTestingWasStarted|MutationTestingWasFinished|MutantsCreationWasStarted|MutantWasCreated|MutantsCreationWasFinished> $actualEvents
     */
    private function assertAreSameEvents(array $expectedEvents, array $actualEvents): void
    {
        $expectedClasses = [
            MutationTestingWasStarted::class,
            MutationTestingWasFinished::class,
            MutantsCreationWasStarted::class,
            MutantWasCreated::class,
            MutantsCreationWasFinished::class,
        ];

        $assertionErrorMessage = sprintf(
            "Expected the following list of events (by class):%s\nGot:%s",
            $this->formatExpectedEvents($expectedEvents),
            $this->formatExpectedEvents($actualEvents)
        );

        foreach ($expectedEvents as $index => $expectedEvent) {
            $this->assertIsInstanceOfAny($expectedClasses, $expectedEvent);
            $this->assertArrayHasKey($index, $actualEvents, $assertionErrorMessage);

            $event = $actualEvents[$index];
            $this->assertInstanceOf(
                get_class($expectedEvent),
                $event,
                $assertionErrorMessage
            );

            if ($expectedEvent instanceof MutantsCreationWasStarted) {
                /* @var MutantsCreationWasStarted $event */
                $this->assertSame($expectedEvent->getMutantCount(), $event->getMutantCount());

                continue;
            }

            if ($expectedEvent instanceof MutationTestingWasStarted) {
                /* @var MutationTestingWasStarted $event */
                $this->assertSame($expectedEvent->getMutationCount(), $event->getMutationCount());
            }
        }

        $this->assertCount(count($expectedEvents), $actualEvents);
    }

    /**
     * @param string[] $expectedClasses
     */
    private function assertIsInstanceOfAny(array $expectedClasses, object $value): void
    {
        $this->assertGreaterThan(
            0,
            count($expectedClasses),
            'Expected to have at least one expected class'
        );

        foreach ($expectedClasses as $expectedClass) {
            if ($value instanceof $expectedClass) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $this->fail(sprintf(
            'Expected to be an instance of any of "%s" but got "%s" instead',
            implode('", "', $expectedClasses),
            get_class($value)
        ));
    }

    /**
     * @param object[] $events
     */
    private function formatExpectedEvents(array $events): string
    {
        if (count($events) === 0) {
            return ' Ø (no events)';
        }

        return "\n - " . implode(
            "\n - ",
            array_map('get_class', $events)
        );
    }
}
