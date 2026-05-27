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

namespace Infection\Tests\Reporter\ShowMetricsReporter;

use Infection\Framework\Str;
use Infection\Metrics\MetricsCalculator;
use Infection\Reporter\Reporter;
use Infection\Reporter\ShowMetricsReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ShowMetricsReporter::class)]
final class ShowMetricsReporterTest extends TestCase
{
    private BufferedOutput $output;

    protected function setUp(): void
    {
        $this->output = new BufferedOutput();
    }

    #[DataProvider('metricsProvider')]
    public function test_it_show_the_metrics(MetricsScenario $scenario): void
    {
        $this->createMetricsCalculator($scenario);

        $reporter = $this->createReporter($scenario);
        $reporter->report();

        $actual = Str::toUnixLineEndings($this->output->fetch());

        $this->assertSame($scenario->expected, $actual);
    }

    public static function metricsProvider(): iterable
    {
        $emptyScenario = new MetricsScenario(
            withUncovered: false,
            killedByTestsCount: 0,
            killedByStaticAnalysisCount: 0,
            errorCount: 0,
            syntaxErrorCount: 0,
            skippedCount: 0,
            ignoredCount: 0,
            escapedCount: 0,
            timedOutCount: 0,
            notTestedCount: 0,
            totalMutantsCount: 0,
            mutationScoreIndicator: 0.,
            coverageRate: 0.,
            coveredCodeMutationScoreIndicator: 0.,
            expected: <<<'DISPLAY'


                0 mutations were generated:
                       0 mutants were killed by Test Framework

                Metrics:
                         Mutation Code Coverage: <low>0%</low>
                         Covered Code MSI: <low>0%</low>

                DISPLAY,
        );

        $completeScenario = new MetricsScenario(
            withUncovered: false,
            killedByTestsCount: 3,
            killedByStaticAnalysisCount: 2,
            errorCount: 1,
            syntaxErrorCount: 10,
            skippedCount: 2,
            ignoredCount: 1,
            escapedCount: 4,
            timedOutCount: 2,
            notTestedCount: 3,
            totalMutantsCount: 6,
            mutationScoreIndicator: 52.,
            coverageRate: 42.,
            coveredCodeMutationScoreIndicator: 74.,
            expected: <<<'DISPLAY'


                6 mutations were generated:
                       3 mutants were killed by Test Framework
                       2 mutants were caught by Static Analysis
                       1 mutants were configured to be ignored
                       3 mutants were not covered by tests
                       4 covered mutants were not detected
                       1 errors were encountered
                      10 syntax errors were encountered
                       2 time outs were encountered
                       2 mutants required more time than configured

                Metrics:
                         Mutation Code Coverage: <low>42%</low>
                         Covered Code MSI: <medium>74%</medium>

                DISPLAY,
        );

        yield 'no metrics' => $emptyScenario->build();

        yield 'no metrics with uncovered' => $emptyScenario
            ->withUncovered(true)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>0%</low>
                             Mutation Code Coverage: <low>0%</low>
                             Covered Code MSI: <low>0%</low>

                    DISPLAY,
            )
            ->build();

        yield 'all metrics' => $completeScenario->build();

        yield 'all metrics with uncovered' => $completeScenario
            ->withUncovered(true)
            ->withExpected(
                <<<'DISPLAY'


                    6 mutations were generated:
                           3 mutants were killed by Test Framework
                           2 mutants were caught by Static Analysis
                           1 mutants were configured to be ignored
                           3 mutants were not covered by tests
                           4 covered mutants were not detected
                           1 errors were encountered
                          10 syntax errors were encountered
                           2 time outs were encountered
                           2 mutants required more time than configured

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>52%</medium>
                             Mutation Code Coverage: <low>42%</low>
                             Covered Code MSI: <medium>74%</medium>

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as low if bellow the low threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(49)
            ->withCoverageRate(49)
            ->withCoveredCodeMutationScoreIndicator(49)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>49%</low>
                             Mutation Code Coverage: <low>49%</low>
                             Covered Code MSI: <low>49%</low>

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as meidum if above the low threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(50)
            ->withCoverageRate(50)
            ->withCoveredCodeMutationScoreIndicator(50)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>50%</medium>
                             Mutation Code Coverage: <medium>50%</medium>
                             Covered Code MSI: <medium>50%</medium>

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as medium if bellow the high threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(89)
            ->withCoverageRate(89)
            ->withCoveredCodeMutationScoreIndicator(89)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <medium>89%</medium>
                             Mutation Code Coverage: <medium>89%</medium>
                             Covered Code MSI: <medium>89%</medium>

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages as high if above the high threshold' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(90)
            ->withCoverageRate(90)
            ->withCoveredCodeMutationScoreIndicator(90)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <high>90%</high>
                             Mutation Code Coverage: <high>90%</high>
                             Covered Code MSI: <high>90%</high>

                    DISPLAY,
            )
            ->build();

        yield 'it marks all percentages based on their respective values' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(40)
            ->withCoverageRate(60)
            ->withCoveredCodeMutationScoreIndicator(95)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>40%</low>
                             Mutation Code Coverage: <medium>60%</medium>
                             Covered Code MSI: <high>95%</high>

                    DISPLAY,
            )
            ->build();

        yield 'it rounds all percentages to the next lowest integer' => $emptyScenario
            ->withUncovered(true)
            ->withMutationScoreIndicator(40.55555555)
            ->withCoverageRate(60.55555555)
            ->withCoveredCodeMutationScoreIndicator(95.5555)
            ->withExpected(
                <<<'DISPLAY'


                    0 mutations were generated:
                           0 mutants were killed by Test Framework

                    Metrics:
                             Mutation Score Indicator (MSI): <low>40%</low>
                             Mutation Code Coverage: <medium>60%</medium>
                             Covered Code MSI: <high>95%</high>

                    DISPLAY,
            )
            ->build();

        yield 'it pads the counts values' => $emptyScenario
            ->withTotalMutantsCount(10_000)
            ->withKilledByTestsCount(3)
            ->withKilledByIgnoredCount(2000)
            ->withExpected(
                <<<'DISPLAY'


                    10000 mutations were generated:
                           3 mutants were killed by Test Framework
                        2000 mutants were configured to be ignored

                    Metrics:
                             Mutation Code Coverage: <low>0%</low>
                             Covered Code MSI: <low>0%</low>

                    DISPLAY,
            )
            ->build();
    }

    private function createReporter(
        MetricsScenario $scenario,
    ): Reporter {
        return new ShowMetricsReporter(
            $this->output,
            $this->createMetricsCalculator($scenario),
            $scenario->withUncovered,
        );
    }

    private function createMetricsCalculator(MetricsScenario $scenario): MetricsCalculator
    {
        $metricsCalculatorMock = $this->createMock(MetricsCalculator::class);

        $metricsCalculatorMock
            ->method('getKilledByTestsCount')
            ->willReturn($scenario->killedByTestsCount);
        $metricsCalculatorMock
            ->method('getKilledByStaticAnalysisCount')
            ->willReturn($scenario->killedByStaticAnalysisCount);
        $metricsCalculatorMock
            ->method('getIgnoredCount')
            ->willReturn($scenario->ignoredCount);
        $metricsCalculatorMock
            ->method('getNotTestedCount')
            ->willReturn($scenario->notTestedCount);
        $metricsCalculatorMock
            ->method('getEscapedCount')
            ->willReturn($scenario->escapedCount);
        $metricsCalculatorMock
            ->method('getErrorCount')
            ->willReturn($scenario->errorCount);
        $metricsCalculatorMock
            ->method('getSyntaxErrorCount')
            ->willReturn($scenario->syntaxErrorCount);
        $metricsCalculatorMock
            ->method('getTimedOutCount')
            ->willReturn($scenario->timedOutCount);
        $metricsCalculatorMock
            ->method('getSkippedCount')
            ->willReturn($scenario->skippedCount);
        $metricsCalculatorMock
            ->method('getTotalMutantsCount')
            ->willReturn($scenario->totalMutantsCount);
        $metricsCalculatorMock
            ->method('getMutationScoreIndicator')
            ->willReturn($scenario->mutationScoreIndicator);
        $metricsCalculatorMock
            ->method('getCoverageRate')
            ->willReturn($scenario->coverageRate);
        $metricsCalculatorMock
            ->method('getCoveredCodeMutationScoreIndicator')
            ->willReturn($scenario->coveredCodeMutationScoreIndicator);

        return $metricsCalculatorMock;
    }
}
