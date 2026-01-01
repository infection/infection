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

use Infection\Framework\Str;
use Infection\Logger\GitLabCodeQualityLogger;
use Infection\Metrics\ResultsCollector;
use Infection\Mutant\DetectionStatus;
use Infection\Mutator\Loop\For_;
use Infection\Tests\EnvVariableManipulation\BacksUpEnvironmentVariables;
use const JSON_THROW_ON_ERROR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function Safe\base64_decode;
use function Safe\json_decode;

#[Group('integration')]
#[CoversClass(GitLabCodeQualityLogger::class)]
final class GitLabCodeQualityLoggerTest extends TestCase
{
    use BacksUpEnvironmentVariables;
    use CreateMetricsCalculator;

    protected function setUp(): void
    {
        $this->backupEnvironmentVariables();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->restoreEnvironmentVariables();
        self::resetOriginalFilePrefix();
    }

    /**
     * @param array<int, array<string, string|array<int|string, array<string, int>|string>>> $expectedContents
     */
    #[DataProvider('metricsProvider')]
    public function test_it_logs_correctly_with_mutations(
        ResultsCollector $resultsCollector,
        array $expectedContents,
    ): void {
        $logger = new GitLabCodeQualityLogger($resultsCollector, null);

        $this->assertLoggedContentIs($expectedContents, $logger);
    }

    public static function metricsProvider(): iterable
    {
        yield 'no mutations; only covered' => [
            new ResultsCollector(),
            [],
        ];

        yield 'all mutations; only covered' => [
            self::createCompleteResultsCollector(),
            [
                [
                    'type' => 'issue',
                    'fingerprint' => 'a1b2c3',
                    'check_name' => 'PregQuote',
                    'description' => 'Escaped Mutant for Mutator PregQuote',
                    'content' => Str::toSystemLineEndings(
                        "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'escaped#1';",
                    ),
                    'categories' => ['Escaped Mutant'],
                    'location' => [
                        'path' => 'foo/bar',
                        'lines' => [
                            'begin' => 9,
                        ],
                    ],
                    'severity' => 'major',
                ],
                [
                    'type' => 'issue',
                    'fingerprint' => 'a1b2c3',
                    'check_name' => 'For_',
                    'description' => 'Escaped Mutant for Mutator For_',
                    'content' => Str::toSystemLineEndings(
                        "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'escaped#0';",
                    ),
                    'categories' => ['Escaped Mutant'],
                    'location' => [
                        'path' => 'foo/bar',
                        'lines' => [
                            'begin' => 10,
                        ],
                    ],
                    'severity' => 'major',
                ],
            ],
        ];

        yield 'Non UTF-8 characters' => [
            self::createNonUtf8CharactersCollector(),
            [
                [
                    'type' => 'issue',
                    'fingerprint' => 'a1b2c3',
                    'check_name' => 'For_',
                    'description' => 'Escaped Mutant for Mutator For_',
                    'content' => Str::toSystemLineEndings(
                        "--- Original\n+++ New\n@@ @@\n\n- echo 'original';\n+ echo 'i?';",
                    ),
                    'categories' => ['Escaped Mutant'],
                    'location' => [
                        'path' => 'foo/bar',
                        'lines' => [
                            'begin' => 10,
                        ],
                    ],
                    'severity' => 'major',
                ],
            ],
        ];
    }

    public function test_it_logs_correctly_with_ci_project_dir(): void
    {
        \Safe\putenv('CI_PROJECT_DIR=/my/project/dir');
        self::setOriginalFilePrefix('/my/project/dir/');

        $resultsCollector = self::createCompleteResultsCollector();

        $logger = new GitLabCodeQualityLogger($resultsCollector, null);

        $this->assertStringContainsString('"path":"foo\/bar"', $logger->getLogLines()[0]);
    }

    public function test_it_logs_correctly_with_custom_project_dir(): void
    {
        \Safe\putenv('CI_PROJECT_DIR=/my/project/dir');
        self::setOriginalFilePrefix('/custom/project/dir/');

        $resultsCollector = self::createCompleteResultsCollector();

        $logger = new GitLabCodeQualityLogger($resultsCollector, '/custom/project/dir/');

        $this->assertStringContainsString('"path":"foo\/bar"', $logger->getLogLines()[0]);
    }

    /**
     * @param array<int, array<string, array<int|string, array<string, int>|string>|string>> $expectedJson
     */
    private function assertLoggedContentIs(array $expectedJson, GitLabCodeQualityLogger $logger): void
    {
        $this->assertSame($expectedJson, json_decode($logger->getLogLines()[0], true, JSON_THROW_ON_ERROR));
    }

    private static function createNonUtf8CharactersCollector(): ResultsCollector
    {
        $collector = new ResultsCollector();

        $collector->collect(
            self::createMutantExecutionResult(
                0,
                For_::class,
                DetectionStatus::ESCAPED,
                base64_decode('abc', true), // produces non UTF-8 character
            ),
        );

        return $collector;
    }
}
