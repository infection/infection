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

namespace Infection\Tests\Reporter;

use function array_key_last;
use Infection\Framework\Str;
use Infection\Metrics\Collector;
use Infection\Metrics\MetricsCalculator;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutant\MutantExecutionResult;
use Infection\Mutator\Loop\For_;
use Infection\Mutator\Regex\PregQuote;
use Infection\Process\MutantProcess;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use function Later\now;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Process\Process;

trait CreateMetricsCalculator
{
    private static string $originalFilePrefix = '';

    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T&Stub
     */
    abstract protected static function createStub(string $type): Stub;

    private static function createCompleteMetricsCalculator(): MetricsCalculator
    {
        $calculator = new MetricsCalculator(2);

        self::feedCollector($calculator);

        return $calculator;
    }

    private static function createCompleteResultsCollector(): ResultsCollector
    {
        $collector = new ResultsCollector();

        self::feedCollector($collector);

        return $collector;
    }

    /**
     * @param non-empty-list<MutantProcess>|null $history
     */
    private static function createEvaluationHistoryResultsCollector(
        bool $withTestAttempt = true,
        DetectionStatus $status = DetectionStatus::ESCAPED,
        ?array $history = null,
    ): ResultsCollector {
        if ($history === null) {
            $history = [self::stubReportedProcess(
                'PHPStan',
                $status,
                'PHPStan output',
                'vendor/bin/phpstan',
                0.25,
            )];

            if ($withTestAttempt) {
                $history = [
                    self::stubReportedProcess(
                        'PHPUnit',
                        DetectionStatus::ESCAPED,
                        "\nPHPUnit output \r\nnext line \n",
                        'vendor/bin/phpunit',
                        1.0,
                    ),
                    ...$history,
                ];
            }
        }

        $lastProcess = $history[array_key_last($history)];
        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withMutantDiff('mutant diff')
            ->withProcessCommandLine($lastProcess->getProcess()->getCommandLine())
            ->withProcessOutput($lastProcess->getResultOutput())
            ->withProcessRuntime($lastProcess->getRuntime())
            ->withDetectionStatus($status)
            ->withEvaluationHistory($history)
            ->build()
        ;
        $collector = new ResultsCollector();
        $collector->collect($result);

        return $collector;
    }

    private static function stubReportedProcess(
        string $name,
        DetectionStatus $status,
        string $output,
        string $commandLine,
        float $runtime,
    ): MutantProcess {
        $process = self::createStub(Process::class);
        $process->method('getCommandLine')->willReturn($commandLine);
        $attempt = self::createStub(MutantProcess::class);
        $attempt->method('getProcess')->willReturn($process);
        $attempt->method('getResult')->willReturn($status);
        $attempt->method('getResultOutput')->willReturn($output);
        $attempt->method('getRuntime')->willReturn($runtime);
        $attempt->method('getTestFrameworkName')->willReturn($name);

        return $attempt;
    }

    private static function feedCollector(Collector $collector): void
    {
        $collector->collect(
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::KILLED_BY_TESTS,
                'killed#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::KILLED_BY_TESTS,
                'killed#1',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::KILLED_BY_STATIC_ANALYSIS,
                'killed by SA#0',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::ERROR,
                'error#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::ERROR,
                'error#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::SYNTAX_ERROR,
                'syntaxError#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::SYNTAX_ERROR,
                'syntaxError#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::ESCAPED,
                'escaped#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::ESCAPED,
                'escaped#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::TIMED_OUT,
                'timedOut#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::TIMED_OUT,
                'timedOut#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::SKIPPED,
                'skipped#0',
            ),
            self::createMutantExecutionResult(
                0,
                PregQuote::class,
                DetectionStatus::SKIPPED,
                'skipped#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::NOT_COVERED,
                'notCovered#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::NOT_COVERED,
                'notCovered#1',
            ),
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::IGNORED,
                'ignored#0',
            ),
            self::createMutantExecutionResult(
                1,
                PregQuote::class,
                DetectionStatus::IGNORED,
                'ignored#1',
            ),
        );
    }

    private static function createMutantExecutionResult(
        int $i,
        string $mutatorClassName,
        DetectionStatus $detectionStatus,
        string $echoMutatedMessage,
        ?string $mutantDiff = null,
        ?string $originalFilePath = null,
    ): MutantExecutionResult {
        return new MutantExecutionResult(
            'bin/phpunit --configuration infection-tmp-phpunit.xml --filter "tests/Acme/FooTest.php"',
            'process output',
            $detectionStatus,
            now(
                $mutantDiff ?? Str::rTrimLines(
                    <<<DIFF
                        --- Original
                        +++ New
                        @@ @@

                        - echo 'original';
                        + echo '$echoMutatedMessage';

                        DIFF,
                ),
            ),
            'a1b2c3',
            $mutatorClassName,
            MutatorName::getName($mutatorClassName),
            $originalFilePath ?? self::$originalFilePrefix . 'foo/bar',
            10 - $i,
            20 - $i,
            10 - $i,
            20 - $i,
            '<?php $a = 1;',
            now('<?php $a = 2;'),
            [],
            0.0,
            [],
        );
    }

    private static function setOriginalFilePrefix(string $pathPrefix): void
    {
        self::$originalFilePrefix = $pathPrefix;
    }

    private static function resetOriginalFilePrefix(): void
    {
        self::$originalFilePrefix = '';
    }
}
