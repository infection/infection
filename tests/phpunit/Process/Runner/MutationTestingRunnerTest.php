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
use ArrayIterator;
use function count;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Differ\DiffSourceCodeMatcher;
use Infection\Event\MutantProcessWasFinished;
use Infection\Event\MutationTestingWasFinished;
use Infection\Event\MutationTestingWasStarted;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Process\Factory\MutantProcessFactory;
use Infection\Process\MutantProcess;
use Infection\Process\Runner\MutationTestingRunner;
use Infection\Process\Runner\ProcessRunner;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutator\MutatorName;
use Infection\Tests\WithConsecutive;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Filesystem\Filesystem;

#[Group('integration')]
#[CoversClass(MutationTestingRunner::class)]
final class MutationTestingRunnerTest extends TestCase
{
    /**
     * @var MutantProcessFactory|MockObject
     */
    private $processFactoryMock;

    /**
     * @var MutantFactory|MockObject
     */
    private $mutantFactoryMock;

    /**
     * @var ProcessRunner|MockObject
     */
    private $processRunnerMock;

    /**
     * @var EventDispatcherCollector
     */
    private $eventDispatcher;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystemMock;

    /**
     * @var MutationTestingRunner
     */
    private $runner;

    protected function setUp(): void
    {
        $this->processFactoryMock = $this->createMock(MutantProcessFactory::class);
        $this->mutantFactoryMock = $this->createMock(MutantFactory::class);
        $this->processRunnerMock = $this->createMock(ProcessRunner::class);
        $this->eventDispatcher = new EventDispatcherCollector();
        $this->fileSystemMock = $this->createMock(Filesystem::class);

        $this->runner = new MutationTestingRunner(
            $this->processFactoryMock,
            $this->mutantFactoryMock,
            $this->processRunnerMock,
            $this->eventDispatcher,
            $this->fileSystemMock,
            new DiffSourceCodeMatcher(),
            false,
            100.0,
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
            $mutation1 = $this->createMutation(1),
            $mutation2 = $this->createMutation(2, 1000.0),
        ];
        $testFrameworkExtraOptions = '--filter=acme/FooTest.php';

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
                [$mutation1],
                [$mutation2],
            ))
            ->willReturnOnConsecutiveCalls(
                $mutant0 = MutantBuilder::build(
                    '/path/to/mutant0',
                    $mutation0,
                    'mutated code 0',
                    'diff0',
                    '<?php $a = 1;',
                ),
                $mutant1 = MutantBuilder::build(
                    '/path/to/mutant1',
                    $mutation1,
                    'mutated code 1',
                    'diff1',
                    '<?php $a = 1;',
                ),
                MutantBuilder::build(
                    '/path/to/mutant2',
                    $mutation2,
                    'mutated code 2',
                    'diff1',
                    '<?php $a = 1;',
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
            ->method('createProcessForMutant')
            ->with(...WithConsecutive::create(
                [$mutant0, $testFrameworkExtraOptions],
                [$mutant1, $testFrameworkExtraOptions],
            ))
            ->willReturnOnConsecutiveCalls(
                $process0 = $this->buildCoveredMutantProcess(),
                $process1 = $this->buildCoveredMutantProcess(),
            )
        ;

        $this->processRunnerMock
            ->expects($this->once())
            ->method('run')
            ->with($this->iterableContaining([$process0, $process1]))
        ;

        $this->runner->run($mutations, $testFrameworkExtraOptions);

        $this->assertAreSameEvents(
            [
                new MutationTestingWasStarted(3, $this->processRunnerMock),
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
                $mutant0 = MutantBuilder::build(
                    '/path/to/mutant0',
                    $mutation0,
                    'mutated code 0',
                    'diff0',
                    '<?php $a = 1;',
                ),
                $mutant1 = MutantBuilder::build(
                    '/path/to/mutant1',
                    $mutation1,
                    'mutated code 1',
                    'diff1',
                    '<?php $a = 1;',
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
            ->method('createProcessForMutant')
            ->with(...WithConsecutive::create(
                [$mutant0, $testFrameworkExtraOptions],
                [$mutant1, $testFrameworkExtraOptions],
            ))
            ->willReturnOnConsecutiveCalls(
                $process0 = $this->buildCoveredMutantProcess(),
                $process1 = $this->buildCoveredMutantProcess(),
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

        $mutant = MutantBuilder::build(
            '/path/to/mutant0',
            $mutation0,
            'mutated code 0',
            '- Assert::integer(1)',
            '<?php $a = 1;',
        );

        $this->mutantFactoryMock
            ->method('create')
            ->with(...WithConsecutive::create(
                [$mutation0],
            ))
            ->willReturnOnConsecutiveCalls($mutant)
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
            array_map('get_class', $events),
        );
    }

    private function buildCoveredMutantProcess(): MutantProcess
    {
        $mutantProcess = $this->createMock(MutantProcess::class);
        $mutantProcess
            ->expects($this->never())
            ->method('getMutant')
        ;

        return $mutantProcess;
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

    private function createMutation(int $i, float $time = 0.01): Mutation
    {
        return new Mutation(
            'path/to/Foo' . $i . '.php',
            [],
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
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    $time,
                ),
            ],
        );
    }
}
