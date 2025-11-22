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

use Infection\Logger\SummaryJsonLogger;
use Infection\Metrics\Collector;
use Infection\Metrics\MetricsCalculator;
use Infection\Mutant\DetectionStatus;
use Infection\Mutator\Loop\For_;
use const JSON_THROW_ON_ERROR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\json_decode;

#[Group('integration')]
#[CoversClass(SummaryJsonLogger::class)]
final class SummaryJsonLoggerTest extends TestCase
{
    use CreateMetricsCalculator;

    /**
     * @param array<string, array<string, int|float>> $expectedContents
     */
    #[DataProvider('metricsProvider')]
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        array $expectedContents,
    ): void {
        $logger = new SummaryJsonLogger($metricsCalculator);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public static function metricsProvider(): iterable
    {
        yield 'no mutations; only covered' => [
            new MetricsCalculator(2),
            [
                'stats' => [
                    'totalMutantsCount' => 0,
                    'killedCount' => 0,
                    'notCoveredCount' => 0,
                    'escapedCount' => 0,
                    'errorCount' => 0,
                    'syntaxErrorCount' => 0,
                    'skippedCount' => 0,
                    'ignoredCount' => 0,
                    'timeOutCount' => 0,
                    'msi' => 0,
                    'mutationCodeCoverage' => 0,
                    'coveredCodeMsi' => 0,
                ],
            ],
        ];

        yield 'all mutations; only covered' => [
            self::createCompleteMetricsCalculator(),
            [
                'stats' => [
                    'totalMutantsCount' => 17,
                    'killedCount' => 2,
                    'notCoveredCount' => 2,
                    'escapedCount' => 2,
                    'errorCount' => 2,
                    'syntaxErrorCount' => 2,
                    'skippedCount' => 2,
                    'ignoredCount' => 2,
                    'timeOutCount' => 2,
                    'msi' => 69.23,
                    'mutationCodeCoverage' => 84.62,
                    'coveredCodeMsi' => 81.82,
                ],
            ],
        ];

        yield 'uncovered mutations' => [
            self::createUncoveredMetricsCalculator(),
            [
                'stats' => [
                    'totalMutantsCount' => 1,
                    'killedCount' => 0,
                    'notCoveredCount' => 1,
                    'escapedCount' => 0,
                    'errorCount' => 0,
                    'syntaxErrorCount' => 0,
                    'skippedCount' => 0,
                    'ignoredCount' => 0,
                    'timeOutCount' => 0,
                    'msi' => 0,
                    'mutationCodeCoverage' => 0,
                    'coveredCodeMsi' => 0,
                ],
            ],
        ];

        yield 'Ignored mutations' => [
            self::createIgnoredMetricsCalculator(),
            [
                'stats' => [
                    'totalMutantsCount' => 1,
                    'killedCount' => 0,
                    'notCoveredCount' => 0,
                    'escapedCount' => 0,
                    'errorCount' => 0,
                    'syntaxErrorCount' => 0,
                    'skippedCount' => 0,
                    'ignoredCount' => 1,
                    'timeOutCount' => 0,
                    'msi' => 0,
                    'mutationCodeCoverage' => 0,
                    'coveredCodeMsi' => 0,
                ],
            ],
        ];
    }

    /**
     * @param array<string, array<string, float|int>> $expectedJson
     */
    private function assertLoggedContentIs(array $expectedJson, SummaryJsonLogger $logger): void
    {
        $this->assertSame($expectedJson, json_decode($logger->getLogLines()[0], true, JSON_THROW_ON_ERROR));
    }

    private static function createUncoveredMetricsCalculator(): MetricsCalculator
    {
        $collector = new MetricsCalculator(2);

        self::initUncoveredCollector($collector);

        return $collector;
    }

    private static function initUncoveredCollector(Collector $collector): void
    {
        $collector->collect(
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::NOT_COVERED,
                'uncovered#0',
            ),
        );
    }

    private static function createIgnoredMetricsCalculator(): MetricsCalculator
    {
        $collector = new MetricsCalculator(2);

        self::initIgnoredCollector($collector);

        return $collector;
    }

    private static function initIgnoredCollector(Collector $collector): void
    {
        $collector->collect(
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::IGNORED,
                'ignored#0',
            ),
        );
    }
}
