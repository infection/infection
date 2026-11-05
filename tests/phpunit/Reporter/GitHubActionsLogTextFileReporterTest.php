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

use Infection\Metrics\ResultsCollector;
use Infection\Reporter\GitHubActionsLogTextFileReporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitHubActionsLogTextFileReporter::class)]
final class GitHubActionsLogTextFileReporterTest extends TestCase
{
    use CreateMetricsCalculator;
    use LineReporterAssertions;

    #[DataProvider('emptyMetricsProvider')]
    public function test_it_reports_results_in_a_text_file_when_there_is_no_mutation(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents,
    ): void {
        $reporter = new GitHubActionsLogTextFileReporter(
            new ResultsCollector(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode,
        );

        $this->assertReportedContentIs($expectedContents, $reporter);
    }

    #[DataProvider('completeMetricsProvider')]
    public function test_it_reports_results_in_a_text_file_when_there_are_mutations(
        bool $debugVerbosity,
        bool $onlyCoveredMode,
        bool $debugMode,
        string $expectedContents,
    ): void {
        $reporter = new GitHubActionsLogTextFileReporter(
            self::createCompleteResultsCollector(),
            $debugVerbosity,
            $onlyCoveredMode,
            $debugMode,
        );

        $this->assertReportedContentIs($expectedContents, $reporter);
    }

    public static function emptyMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'debug verbosity; no debug mode' => [
            true,
            false,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'debug verbosity; debug mode' => [
            true,
            false,
            true,
            <<<'TXT'
                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

                0 Not Covered mutants

                TXT,
        ];

        yield 'no debug verbosity; no debug mode; only covered' => [
            false,
            true,
            false,
            <<<'TXT'
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                TXT,
        ];

        yield 'debug verbosity; no debug mode; only covered' => [
            true,
            true,
            false,
            <<<'TXT'
                Note: Pass `--debug` to log test-framework output.

                0 Escaped mutants

                0 Timed Out mutants

                0 Skipped mutants

                0 Killed by Test Framework mutants

                0 Killed by Static Analysis mutants

                0 Errors mutants

                0 Syntax Errors mutants

                TXT,
        ];
    }

    public static function completeMetricsProvider(): iterable
    {
        yield 'no debug verbosity; no debug mode' => [
            false,
            false,
            false,
            <<<TXT
                Note: Pass `--log-verbosity=all` to log information about killed and errored mutants.
                Note: Pass `--debug` to log test-framework output.

                ::group::Escaped mutants
                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'escaped#1';\e[0m


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'escaped#0';\e[0m
                ::endgroup::

                ::group::Timed Out mutants
                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'timedOut#1';\e[0m


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'timedOut#0';\e[0m
                ::endgroup::

                ::group::Skipped mutants
                1) foo/bar:10    [M] For_ [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'skipped#0';\e[0m


                2) foo/bar:10    [M] PregQuote [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'skipped#1';\e[0m
                ::endgroup::

                ::group::Not Covered mutants
                1) foo/bar:9    [M] PregQuote [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'notCovered#1';\e[0m


                2) foo/bar:10    [M] For_ [ID] a1b2c3

                \e[31m--- Original\e[0m
                \e[32m+++ New\e[0m
                @@ @@

                \e[31m- echo 'original';\e[0m
                \e[32m+ echo 'notCovered#0';\e[0m
                ::endgroup::


                TXT,
        ];
    }
}
