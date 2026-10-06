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

namespace Infection\Tests\Report;

use Infection\Event\EventDispatcher\EventCollectingEventDispatcher;
use Infection\Event\EventDispatcher\SyncEventDispatcher;
use Infection\Event\Events\Application\ApplicationExecutionWasFinished;
use Infection\Event\Events\Application\ApplicationExecutionWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisRunWasStarted;
use Infection\Event\Events\ArtefactCollection\InitialStaticAnalysis\InitialStaticAnalysisSubStepWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestCaseWasCompleted;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasFinished;
use Infection\Event\Events\ArtefactCollection\InitialTestExecution\InitialTestSuiteWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantProcessWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutableFileWasProcessed;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationGeneration\MutationGenerationWasStarted;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasFinished;
use Infection\Event\Events\MutationAnalysis\MutationTestingWasStarted;
use Infection\Mutator\Loop\For_;
use Infection\Process\Runner\DryProcessRunner;
use Infection\Report\DebugEventsDataProducer;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use Later\Interfaces\Deferred;
use function Later\later;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Pipeline\take;
use function Safe\json_decode;
use stdClass;

#[CoversClass(DebugEventsDataProducer::class)]
final class DebugEventsDataProducerTest extends TestCase
{
    /**
     * @param list<object> $events
     * @param list<array<string, mixed>> $expected
     */
    #[DataProvider('tracesProvider')]
    public function test_it_produces_the_complete_trace_in_dispatch_order(
        array $events,
        array $expected,
    ): void {
        $collectingDispatcher = $this->createEventDispatcher($events);
        $producer = new DebugEventsDataProducer($collectingDispatcher);

        $actual = take($producer->produce())
            ->cast(
                static fn (string $record) => json_decode(
                    $record,
                    true,
                ),
            )
            ->toList()
        ;

        $this->assertSame($expected, $actual);
    }

    public static function tracesProvider(): iterable
    {
        yield 'no events' => [[], []];

        yield 'application events in dispatch order' => [
            [
                new ApplicationExecutionWasStarted(),
                new ApplicationExecutionWasFinished(),
            ],
            [
                [
                    'sequence' => 1,
                    'event' => ApplicationExecutionWasStarted::class,
                    'data' => [],
                ],
                [
                    'sequence' => 2,
                    'event' => ApplicationExecutionWasFinished::class,
                    'data' => [],
                ],
            ],
        ];

        foreach (self::eventsProvider() as $name => [$event, $expectedData]) {
            yield $name => [
                [$event],
                [
                    [
                        'sequence' => 1,
                        'event' => $event::class,
                        'data' => $expectedData,
                    ],
                ],
            ];
        }
    }

    private static function eventsProvider(): iterable
    {
        foreach ([
            new ApplicationExecutionWasStarted(),
            new ApplicationExecutionWasFinished(),
            new InitialTestCaseWasCompleted(),
            new InitialStaticAnalysisRunWasStarted(),
            new InitialStaticAnalysisSubStepWasCompleted(),
            new MutationGenerationWasFinished(),
            new MutationTestingWasFinished(),
            new stdClass(),
        ] as $event) {
            yield $event::class => [$event, []];
        }

        yield 'initial test command' => [
            new InitialTestSuiteWasStarted('phpunit --coverage-xml=coverage'),
            ['commandLine' => 'phpunit --coverage-xml=coverage'],
        ];

        yield 'initial test output with newlines' => [
            new InitialTestSuiteWasFinished("Tests: 2\nOK"),
            ['outputText' => "Tests: 2\nOK"],
        ];

        yield 'initial test output with invalid UTF-8' => [
            new InitialTestSuiteWasFinished("output: \xFF"),
            ['outputText' => "output: \u{FFFD}"],
        ];

        yield 'initial static analysis output' => [
            new InitialStaticAnalysisRunWasFinished('No errors'),
            ['outputText' => 'No errors'],
        ];

        yield 'initial static analysis output with invalid UTF-8' => [
            new InitialStaticAnalysisRunWasFinished("output: \xFF"),
            ['outputText' => "output: \u{FFFD}"],
        ];

        yield 'mutation generation count' => [
            new MutationGenerationWasStarted(7),
            ['mutableFilesCount' => 7],
        ];

        yield 'processed file and mutation hashes' => [
            new MutableFileWasProcessed(
                'src/Foo.php',
                ['first-hash', 'second-hash'],
            ),
            [
                'sourceFilePath' => 'src/Foo.php',
                'mutationHashes' => ['first-hash', 'second-hash'],
            ],
        ];

        yield 'mutation testing count and runner type' => [
            new MutationTestingWasStarted(
                3,
                new DryProcessRunner(),
            ),
            [
                'mutationCount' => 3,
                'processRunner' => DryProcessRunner::class,
            ],
        ];

        yield 'mutation identification without its AST' => [
            new MutationEvaluationWasStarted(
                MutationBuilder::withMinimalTestData()
                    ->withHash('mutation-id')
                    ->build(),
            ),
            [
                'mutation' => [
                    'hash' => 'mutation-id',
                    'mutatorClass' => For_::class,
                    'mutatorName' => 'For_',
                    'originalFilePath' => 'src/Foo.php',
                    'attributes' => [
                        'startLine' => 10,
                        'endLine' => 15,
                        'startTokenPos' => 0,
                        'endTokenPos' => 8,
                        'startFilePos' => 2,
                        'endFilePos' => 4,
                    ],
                    'coveredByTests' => false,
                ],
            ],
        ];

        yield 'mutant execution result without forcing lazy code or diffs' => self::createMutantExecutionResultScenario(
            processOutput: '',
            expectedProcessOutput: '',
        );

        yield 'mutant execution result with invalid UTF-8' => self::createMutantExecutionResultScenario(
            processOutput: "output: \xFF",
            expectedProcessOutput: "output: \u{FFFD}",
        );
    }

    /**
     * @return array{MutantProcessWasFinished, array<string, mixed>}
     */
    private static function createMutantExecutionResultScenario(
        string $processOutput,
        string $expectedProcessOutput,
    ): array {
        $deferred = self::createDeferredThatMustNotBeEvaluated();

        return [
            new MutantProcessWasFinished(
                MutantExecutionResultBuilder::withMinimalTestData()
                    ->withProcessOutput($processOutput)
                    ->withMutantDiff($deferred)
                    ->withMutatedCode($deferred)
                    ->build(),
            ),
            [
                'executionResult' => [
                    'mutantHash' => 'abc123def456',
                    'mutatorClass' => For_::class,
                    'mutatorName' => 'For_',
                    'originalFilePath' => 'src/Foo.php',
                    'originalStartingLine' => 10,
                    'originalEndingLine' => 15,
                    'detectionStatus' => 'killed by tests',
                    'processCommandLine' => 'vendor/bin/phpunit --configuration phpunit.xml',
                    'processOutput' => $expectedProcessOutput,
                    'processRuntime' => 0.123,
                ],
            ],
        ];
    }

    /**
     * @return Deferred<string>
     */
    private static function createDeferredThatMustNotBeEvaluated(): Deferred
    {
        return later(static function (): iterable {
            yield self::fail(
                'Producing a report must leave lazy mutant code and diffs unevaluated.',
            );
        });
    }

    private function createEventDispatcher(array $events): EventCollectingEventDispatcher
    {
        $eventDispatcher = new EventCollectingEventDispatcher(
            new SyncEventDispatcher(),
        );

        foreach ($events as $event) {
            $eventDispatcher->dispatch($event);
        }

        return $eventDispatcher;
    }
}
