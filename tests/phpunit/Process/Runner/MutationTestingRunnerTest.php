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

use function array_fill;
use function array_map;
use ArrayIterator;
use function count;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Differ\DiffSourceCodeMatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\Factory\MutantProcessContainerFactory;
use Infection\Process\MutantProcess;
use Infection\Process\MutantProcessContainer;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\ProcessRunner;
use Infection\Testing\MutatorName;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\WithConsecutive;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(MutationTestingRunner::class)]
final class MutationTestingRunnerTest extends TestCase
{
    private const TIMEOUT = 100.0;

    private MockObject&MutantProcessContainerFactory $processFactoryMock;

    private MockObject&MutantFactory $mutantFactoryMock;

    private MockObject&ProcessRunner $processRunnerMock;

    private EventDispatcherCollector $eventDispatcher;

    private MockObject&Filesystem $fileSystemMock;

    private MockObject&DiffSourceCodeMatcher $diffSourceCodeMatcher;

    private MutationTestingRunner $runner;

    protected function setUp(): void
    {
        $this->processFactoryMock = $this->createMock(MutantProcessContainerFactory::class);
        $this->mutantFactoryMock = $this->createMock(MutantFactory::class);
        $this->processRunnerMock = $this->createMock(ProcessRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();
        $this->fileSystemMock = $this->createMock(Filesystem::class);
        $this->diffSourceCodeMatcher = $this->createMock(DiffSourceCodeMatcher::class);

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            $this->diffSourceCodeMatcher,
            false,
            self::TIMEOUT,
            [],
        );
    }

    public function test_it_does_not_create_processes_when_there_is_not_mutations(): void
    {
        $mutations = [];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->emptyIterable())
        ;

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(0, $this->processRunnerMock),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_applies_and_run_the_mutations(): void
    {
        $mutations = [
            $mutation0 = $this->createMutation(0),
            $mutation1 = $this->createMutation(1, self::TIMEOUT - 1.0),
            $mutation2 = $this->createMutation(2, self::TIMEOUT),
            $mutation3 = $this->createMutation(3, coveredByTests: false),
        ];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
                [$mutation1],
                [$mutation2],
                [$mutation3],
            ))
            ->willReturnOnConsecutiveCalls(
                $mutant0 = MutantBuilder::materialize(
                    '/path/to/mutant0',
                    $mutation0,
                    'mutated code 0',
                ),
                $mutant1 = MutantBuilder::materialize(
                    '/path/to/mutant1',
                    $mutation1,
                    'mutated code 1',
                ),
                MutantBuilder::materialize(
                    mutation: $mutation2,
                ),
                MutantBuilder::materialize(
                    mutation: $mutation3,
                ),
            )
        ;

        $this->fileSystemMock
            ->expects($this->exactly(2))
            ->method('dumpFile')
            ->with(...WithConsecutive::create(
                ['/path/to/mutant0', 'mutated code 0'],
                ['/path/to/mutant1', 'mutated code 1'],
            ))
        ;

        $this->processFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutant0, $testFrameworkExtraOptions],
                [$mutant1, $testFrameworkExtraOptions],
            ))
            ->willReturnOnConsecutiveCalls(
                $process0 = $this->buildCoveredMutantProcessContainer(),
                $process1 = $this->buildCoveredMutantProcessContainer(),
            )
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->iterableContaining([$process0, $process1]))
        ;

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $ignoredMutantCount = 2;

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(4, $this->processRunnerMock),
                ...array_fill(
                    0,
                    $ignoredMutantCount,
                    $this->createMock(MutantProcessWasFinished::class),
                ),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );

        /** @var MutantProcessWasFinished $secondSkippedEvent */
        $secondSkippedEvent = $this->eventDispatcher->getEvents()[2];

        $this->assertSame(
            DetectionStatus::NOT_COVERED,
            $secondSkippedEvent->getExecutionResult()->getDetectionStatus(),
            'Mutations should be processed in the order they are given',
        );
    }

    public function test_it_applies_and_run_only_matched_by_id_mutants(): void
    {
        $mutations = [
            $mutation0 = $this->createMutation(0),
            $this->createMutation(1, self::TIMEOUT - 1.0),
            $this->createMutation(2, self::TIMEOUT),
            $this->createMutation(3, coveredByTests: false),
        ];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
            ))
            ->willReturn(
                $mutant0 = MutantBuilder::materialize(
                    '/path/to/mutant0',
                    $mutation0,
                    'mutated code 0',
                ),
            )
        ;

        $this->fileSystemMock
            ->expects($this->exactly(1))
            ->method('dumpFile')
            ->with(...WithConsecutive::create(
                ['/path/to/mutant0', 'mutated code 0'],
            ))
        ;

        $this->processFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutant0, $testFrameworkExtraOptions],
            ))
            ->willReturn(
                $process0 = $this->buildCoveredMutantProcessContainer(),
            )
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->iterableContaining([$process0]))
            ->willReturn([$process0])
        ;

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            $this->diffSourceCodeMatcher,
            false,
            self::TIMEOUT,
            [],
            'fd952823181329ed33260b45eb3aa956', // mutation with index 0
        );

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(4, $this->processRunnerMock),
                $this->createMock(MutantProcessWasFinished::class),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_applies_and_run_the_mutations_when_concurrent_execution_requested(): void
    {
        $mutations = new ArrayIterator([
            $mutation0 = $this->createMutation(0),
            $mutation1 = $this->createMutation(1),
        ]);

        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
                [$mutation1],
            ))
            ->willReturnOnConsecutiveCalls(
                $mutant0 = MutantBuilder::materialize(
                    '/path/to/mutant0',
                    $mutation0,
                    'mutated code 0',
                ),
                $mutant1 = MutantBuilder::materialize(
                    '/path/to/mutant1',
                    $mutation1,
                    'mutated code 1',
                ),
            )
        ;

        $this->fileSystemMock
            ->expects($this->exactly(2))
            ->method('dumpFile')
            ->with(...WithConsecutive::create(
                ['/path/to/mutant0', 'mutated code 0'],
                ['/path/to/mutant1', 'mutated code 1'],
            ))
        ;

        $this->processFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutant0, $testFrameworkExtraOptions],
                [$mutant1, $testFrameworkExtraOptions],
            ))
            ->willReturnOnConsecutiveCalls(
                $process0 = $this->buildCoveredMutantProcessContainer(),
                $process1 = $this->buildCoveredMutantProcessContainer(),
            )
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->iterableContaining([$process0, $process1]))
        ;

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            new DiffSourceCodeMatcher(),
            true,
            100.0,
            [],
        );

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(0, $this->processRunnerMock),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_does_not_create_processes_when_code_is_ignored_by_regex(): void
    {
        $mutations = new ArrayIterator([
            $mutation0 = $this->createMutation(0),
        ]);

        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $mutant = MutantBuilder::materialize(
            '/path/to/mutant0',
            $mutation0,
            'mutated code 0',
            '- Assert::integer(1)',
        );

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
            ))
            ->willReturn($mutant)
        ;

        $this->fileSystemMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->emptyIterable())
        ;

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            new DiffSourceCodeMatcher(),
            true,
            100.0,
            [
                'For_' => ['Assert::.*'],
            ],
        );

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(0, $this->processRunnerMock),
                new MutantProcessWasFinished(MutantExecutionResult::createFromNonCoveredMutant($mutant)),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_does_not_create_processes_when_mutants_are_not_matched_by_mutant_id(): void
    {
        $mutations = new ArrayIterator([
            $mutation0 = $this->createMutation(0),
        ]);

        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $mutant = MutantBuilder::materialize(
            '/path/to/mutant0',
            $mutation0,
            'mutated code 0',
            '- Assert::integer(1)',
        );

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
            ))
            ->willReturn($mutant)
        ;

        $this->fileSystemMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->emptyIterable())
        ;

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            new DiffSourceCodeMatcher(),
            true,
            100.0,
            [
                'For_' => ['Assert::.*'],
            ],
            'mutant-id-1',
        );

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(0, $this->processRunnerMock),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_keeps_mutations_if_diff_matcher_does_not_match(): void
    {
        $this->diffSourceCodeMatcher
            ->expects($this->once())
            ->method('matches')
            ->willReturn(false);

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            $this->diffSourceCodeMatcher,
            true,
            100.0,
            [
                'For_' => ['Assert::.*'],
            ],
        );

        $mutation = $this->createMutation(0);

        $mutant = MutantBuilder::materialize(
            mutation: $mutation,
            mutatedCode: 'mutated code 0',
        );

        $result = $this->invokeMethod('ignoredByRegex', $mutant);

        $this->assertTrue($result);
        $this->assertCount(0, $this->eventDispatcher->getEvents());
    }

    public function test_it_passes_through_iterables_when_concurrent_execution_requested(): void
    {
        $mutations = new ArrayIterator();

        $this->mutantFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->someIterable())
        ;

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            new DiffSourceCodeMatcher(),
            true,
            100.0,
            [],
        );

        $this->runner->run($mutations, '');
    }

    public function test_it_dispatches_events_even_when_no_mutations_is_given(): void
    {
        $mutations = [];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->processFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->mutantFactoryMock
            ->expects($this->never())
            ->method($this->anything())
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->emptyIterable())
        ;

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(0, $this->processRunnerMock),
                new MutationTestingWasFinished(),
            ],
            $this->eventDispatcher->getEvents(),
        );
    }

    public function test_it_emits_mutant_process_finished_for_uncovered_mutations(): void
    {
        $mutation = $this->createMutation(0, coveredByTests: false);

        $mutant = MutantBuilder::materialize(mutation: $mutation);

        $result = $this->invokeMethod('uncoveredByTest', $mutant);

        $this->assertFalse($result);
        $this->assertHasEvent(MutantProcessWasFinished::class, $this->eventDispatcher->getEvents());
    }

    public function test_container_to_finished_event(): void
    {
        $result = $this->createMock(MutantExecutionResult::class);

        $process = $this->createMock(MutantProcess::class);
        $process->expects($this->once())
            ->method('getMutantExecutionResult')
            ->willReturn($result);

        $container = $this->createMock(MutantProcessContainer::class);
        $container->expects($this->once())
            ->method('getCurrent')
            ->willReturn($process);

        $result = $this->invokeMethod('containerToFinishedEvent', $container);
        $this->assertInstanceOf(MutantProcessWasFinished::class, $result);
    }

    private function invokeMethod(string $methodName, mixed ...$args): mixed
    {
        $method = (new ReflectionClass($this->runner))->getMethod($methodName);

        return $method->invoke($this->runner, ...$args);
    }

    /**
     * @param class-string $expectedEventClass
     * @param array<object> $actualEvents
     */
    private function assertHasEvent(
        string $expectedEventClass,
        array $actualEvents,
    ): void {
        $this->assertContains(
            $expectedEventClass,
            array_map(get_class(...), $actualEvents),
        );
    }

    /**
     * @param array<MutationTestingWasStarted|MutationTestingWasFinished> $expectedEvents
     * @param array<MutationTestingWasStarted|MutationTestingWasFinished> $actualEvents
     */
    private function assertAreSameEvents(array $expectedEvents, array $actualEvents): void
    {
        $expectedClasses = [
            MutationTestingWasStarted::class,
            MutationTestingWasFinished::class,
            MutantProcessWasFinished::class,
        ];

        $assertionErrorMessage = sprintf(
            "Expected the following list of events (by class):%s\nGot:%s",
            $this->formatExpectedEvents($expectedEvents),
            $this->formatExpectedEvents($actualEvents),
        );

        foreach ($expectedEvents as $index => $expectedEvent) {
            $this->assertIsInstanceOfAny($expectedClasses, $expectedEvent);
            $this->assertArrayHasKey($index, $actualEvents, $assertionErrorMessage);

            $expectedEventClass = $expectedEvent::class;

            // Handle mocks
            foreach ($expectedClasses as $expectedClassName) {
                if ($expectedEvent instanceof $expectedClassName) {
                    $expectedEventClass = $expectedClassName;
                }
            }

            $event = $actualEvents[$index];
            $this->assertInstanceOf(
                $expectedEventClass,
                $event,
                $assertionErrorMessage,
            );

            if ($expectedEvent instanceof MutationTestingWasStarted) {
                /* @var MutationTestingWasStarted $event */
                $this->assertSame($expectedEvent->getMutationCount(), $event->getMutationCount(), 'Mutation count is not matching');
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
            'Expected to have at least one expected class',
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
            $value::class,
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
            array_map(get_class(...), $events),
        );
    }

    private function buildCoveredMutantProcessContainer(): MutantProcessContainer
    {
        return $this->createMock(MutantProcessContainer::class);
    }

    private function someIterable(?callable $callback = null): Callback
    {
        return $this->callback(static function (iterable $subject) use ($callback) {
            if ($callback !== null) {
                return $callback($subject);
            }

            return true;
        });
    }

    private function emptyIterable(): Callback
    {
        return $this->someIterable(static function (iterable $subject): bool {
            foreach ($subject as $value) {
                return false;
            }

            return true;
        });
    }

    /**
     * @param MutantProcessContainer[] $expected
     */
    private function iterableContaining(array $expected): Callback
    {
        return $this->someIterable(static function (iterable $subject) use ($expected): bool {
            $actual = [];

            foreach ($subject as $value) {
                $actual[] = $value;
            }

            return $expected === $actual;
        });
    }

    private function createMutation(int $i, float $time = 0.01, bool $coveredByTests = true): Mutation
    {
        return new Mutation(
            'path/to/Foo' . $i . '.php',
            [],
            For_::class,
            MutatorName::getName(For_::class),
            [
                'startLine' => $i,
                'endLine' => 15,
                'startTokenPos' => 0,
                'endTokenPos' => 8,
                'startFilePos' => 2,
                'endFilePos' => 4,
            ],
            'Unknown',
            MutatedNode::wrap(new Nop()),
            0,
            $coveredByTests ? [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    $time,
                ),
            ] : [],
            [],
            '',
        );
    }
}
