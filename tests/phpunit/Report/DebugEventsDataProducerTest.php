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
use function Safe\json_decode;
use stdClass;

#[CoversClass(DebugEventsDataProducer::class)]
final class DebugEventsDataProducerTest extends TestCase
{
    /**
     * @param array<string, mixed> $expectedData
     */
    #[DataProvider('eventsProvider')]
    public function test_it_records_events_with_explicit_payloads(
        object $event,
        array $expectedData,
    ): void {
        $producer = new DebugEventsDataProducer();
        $producer->recordEvent($event);

        $line = $producer->produce();

        $this->assertStringEndsWith(
            "\n",
            $line,
            'Each event must be a complete JSONL record.',
        );
        $this->assertEquals(
            (object) [
                'sequence' => 1,
                'event' => $event::class,
                'data' => (object) $expectedData,
            ],
            json_decode($line),
            'The trace must identify the event and serialize its debugging payload.',
        );
    }

    public static function eventsProvider(): iterable
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

        yield 'initial static analysis output' => [
            new InitialStaticAnalysisRunWasFinished('No errors'),
            ['outputText' => 'No errors'],
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
                'mutation' => (object) [
                    'hash' => 'mutation-id',
                    'mutatorClass' => For_::class,
                    'mutatorName' => 'For_',
                    'originalFilePath' => 'src/Foo.php',
                    'attributes' => (object) [
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

        yield 'mutant execution result' => [
            new MutantProcessWasFinished(
                MutantExecutionResultBuilder::withMinimalTestData()->build(),
            ),
            [
                'executionResult' => (object) [
                    'mutantHash' => 'abc123def456',
                    'mutatorClass' => For_::class,
                    'mutatorName' => 'For_',
                    'originalFilePath' => 'src/Foo.php',
                    'originalStartingLine' => 10,
                    'originalEndingLine' => 15,
                    'detectionStatus' => 'killed by tests',
                    'processCommandLine' => 'vendor/bin/phpunit --configuration phpunit.xml',
                    'processOutput' => '',
                    'processRuntime' => 0.123,
                ],
            ],
        ];

        yield 'invalid UTF-8 in process output' => [
            new InitialTestSuiteWasFinished("output: \xFF"),
            ['outputText' => "output: \u{FFFD}"],
        ];
    }

    public function test_it_consumes_pending_records_and_keeps_the_sequence_between_reports(): void
    {
        $producer = new DebugEventsDataProducer();
        $producer->recordEvent(new ApplicationExecutionWasStarted());
        $producer->recordEvent(new ApplicationExecutionWasFinished());

        $this->assertSame(
            '{"sequence":1,"event":"Infection\\\\Event\\\\Events\\\\Application\\\\ApplicationExecutionWasStarted","data":{}}' . "\n"
            . '{"sequence":2,"event":"Infection\\\\Event\\\\Events\\\\Application\\\\ApplicationExecutionWasFinished","data":{}}' . "\n",
            $producer->produce(),
            'Pending events must be serialized in the order they were received.',
        );
        $this->assertSame(
            '',
            $producer->produce(),
            'Producing a report must consume its pending records.',
        );

        $producer->recordEvent(new ApplicationExecutionWasStarted());

        $this->assertSame(
            '{"sequence":3,"event":"Infection\\\\Event\\\\Events\\\\Application\\\\ApplicationExecutionWasStarted","data":{}}' . "\n",
            $producer->produce(),
            'The sequence must continue across reports without replaying consumed events.',
        );
    }

    public function test_it_does_not_force_lazy_code_or_diffs(): void
    {
        $evaluated = false;

        /** @var Deferred<string> $deferred */
        $deferred = later(static function () use (&$evaluated): iterable {
            $evaluated = true;

            yield 'Lazy code or diff';
        });

        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withMutantDiff($deferred)
            ->withMutatedCode($deferred)
            ->build()
        ;
        $producer = new DebugEventsDataProducer();
        $producer->recordEvent(new MutantProcessWasFinished($result));
        $producer->produce();

        $this->assertFalse($evaluated);
    }
}
