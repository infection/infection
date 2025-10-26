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

use Infection\Logger\PerMutatorLogger;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PerMutatorLogger::class)]
final class PerMutatorLoggerTest extends TestCase
{
    use CreateMetricsCalculator;
    use LineLoggerAssertions;

    #[DataProvider('metricsProvider')]
    public function test_it_logs_correctly_with_mutations(
        MetricsCalculator $metricsCalculator,
        ResultsCollector $resultsCollector,
        string $expectedContents,
    ): void {
        $logger = new PerMutatorLogger($metricsCalculator, $resultsCollector, 20);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public static function metricsProvider(): iterable
    {
        yield 'no mutations' => [
            new MetricsCalculator(2),
            new ResultsCollector(),
            <<<'TXT'
                # Effects per Mutator

                | Mutator | Mutations | Killed by Test Framework | Test Timings min/avg/max | Killed by Static Analysis | Static Analysis Timings min/avg/max | Escaped | Errors | Syntax Errors | Timed Out (Limit: 20 secs) | Skipped | Ignored | MSI (%s) | Covered MSI (%s) |
                | ------- | --------- | ------------------------ | ------------------------ | ------------------------- | ----------------------------------- | ------- | ------ | ------------- | -------------------------- | ------- | ------- | -------- | ---------------- |

                TXT,
        ];

        yield 'all mutations' => [
            self::createCompleteMetricsCalculator(),
            self::createCompleteResultsCollector(),
            <<<'TXT'
                # Effects per Mutator

                | Mutator   | Mutations | Killed by Test Framework | Test Timings min/avg/max | Killed by Static Analysis | Static Analysis Timings min/avg/max | Escaped | Errors | Syntax Errors | Timed Out (Limit: 20 secs) | Skipped | Ignored | MSI (%s) | Covered MSI (%s) |
                | --------- | --------- | ------------------------ | ------------------------ | ------------------------- | ----------------------------------- | ------- | ------ | ------------- | -------------------------- | ------- | ------- | -------- | ---------------- |
                | For_      |         8 |                        1 |  0.00 / 0.00 / 0.00 secs |                         0 |             0.00 / 0.00 / 0.00 secs |       1 |      1 |             1 |                          1 |       1 |       1 |    66.67 |            80.00 |
                | PregQuote |         9 |                        1 |  0.00 / 0.00 / 0.00 secs |                         1 |             0.00 / 0.00 / 0.00 secs |       1 |      1 |             1 |                          1 |       1 |       1 |    71.43 |            83.33 |

                TXT,
        ];
    }
}
