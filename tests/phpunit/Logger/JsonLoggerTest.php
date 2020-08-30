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

namespace Infection\Tests\Logger;

use Infection\Logger\JsonLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\Mutator\ZeroIteration\For_;
use const JSON_THROW_ON_ERROR;
use const PHP_EOL;
use PHPUnit\Framework\TestCase;
use function Safe\json_decode;
use function str_replace;

/**
 * @group integration
 */
final class JsonLoggerTest extends TestCase
{
    use CreateMetricsCalculator;

    /**
     * @dataProvider metricsProvider
     */
    public function test_it_logs_correctly_with_mutations(
        bool $onlyCovered,
        MetricsCalculator $metricsCalculator,
        array $expectedContents
    ): void {
        $logger = new JsonLogger($metricsCalculator, $onlyCovered);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public function metricsProvider(): iterable
    {
        yield 'no mutations; only covered' => [
            true,
            new MetricsCalculator(2),
            [
                'stats' => [
                    'totalMutantsCount' => 0,
                    'killedCount' => 0,
                    'notCoveredCount' => 0,
                    'escapedCount' => 0,
                    'errorCount' => 0,
                    'skippedCount' => 0,
                    'timeOutCount' => 0,
                    'msi' => 0,
                    'mutationCodeCoverage' => 0,
                    'coveredCodeMsi' => 0,
                ],
                'escaped' => [],
                'timeouted' => [],
                'killed' => [],
                'errored' => [],
                'uncovered' => [],
            ],
        ];

        yield 'all mutations; only covered' => [
            true,
            $this->createCompleteMetricsCalculator(),
            [
                'stats' => [
                    'totalMutantsCount' => 12,
                    'killedCount' => 2,
                    'notCoveredCount' => 2,
                    'escapedCount' => 2,
                    'errorCount' => 2,
                    'skippedCount' => 2,
                    'timeOutCount' => 2,
                    'msi' => 60,
                    'mutationCodeCoverage' => 80,
                    'coveredCodeMsi' => 75,
                ],
                'escaped' => [
                    [
                        'mutator' => [
                            'mutatorName' => 'PregQuote',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 9,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'escaped#1';"),
                        'processOutput' => 'process output',
                    ],
                    [
                        'mutator' => [
                            'mutatorName' => 'For_',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 10,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'escaped#0';"),
                        'processOutput' => 'process output',
                    ],
                ],
                'timeouted' => [
                    [
                        'mutator' => [
                            'mutatorName' => 'PregQuote',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 9,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'timedOut#1';"),
                        'processOutput' => 'process output',
                    ],
                    [
                        'mutator' => [
                            'mutatorName' => 'For_',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 10,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'timedOut#0';"),
                        'processOutput' => 'process output',
                    ],
                ],
                'killed' => [
                    [
                        'mutator' => [
                            'mutatorName' => 'PregQuote',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 9,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'killed#1';"),
                        'processOutput' => 'process output',
                    ],
                    [
                        'mutator' => [
                            'mutatorName' => 'For_',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 10,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'killed#0';"),
                        'processOutput' => 'process output',
                    ],
                ],
                'errored' => [
                    [
                        'mutator' => [
                            'mutatorName' => 'PregQuote',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 9,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'error#1';"),
                        'processOutput' => 'process output',
                    ],
                    [
                        'mutator' => [
                            'mutatorName' => 'For_',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 10,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'error#0';"),
                        'processOutput' => 'process output',
                    ],
                ],
                'uncovered' => [],
            ],
        ];

        yield 'uncovered mutations' => [
            false,
            $this->createUncoveredMetricsCalculator(),
            [
                'stats' => [
                    'totalMutantsCount' => 1,
                    'killedCount' => 0,
                    'notCoveredCount' => 1,
                    'escapedCount' => 0,
                    'errorCount' => 0,
                    'skippedCount' => 0,
                    'timeOutCount' => 0,
                    'msi' => 0,
                    'mutationCodeCoverage' => 0,
                    'coveredCodeMsi' => 0,
                ],
                'escaped' => [],
                'timeouted' => [],
                'killed' => [],
                'errored' => [],
                'uncovered' => [
                    [
                        'mutator' => [
                            'mutatorName' => 'For_',
                            'originalSourceCode' => '<?php $a = 1;',
                            'mutatedSourceCode' => '<?php $a = 2;',
                            'originalFilePath' => 'foo/bar',
                            'originalStartLine' => 10,
                        ],
                        'diff' => str_replace("\n", PHP_EOL, "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'uncovered#0';"),
                        'processOutput' => 'process output',
                    ],
                ],
            ],
        ];
    }

    private function assertLoggedContentIs(array $expectedJson, JsonLogger $logger): void
    {
        $this->assertSame($expectedJson, json_decode($logger->getLogLines()[0], true, JSON_THROW_ON_ERROR));
    }

    private function createUncoveredMetricsCalculator(): MetricsCalculator
    {
        $calculator = new MetricsCalculator(2);

        $calculator->collect(
            $this->createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::NOT_COVERED,
                'uncovered#0'
            ),
        );

        return $calculator;
    }
}
