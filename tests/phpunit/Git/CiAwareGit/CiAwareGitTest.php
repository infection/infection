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

namespace Infection\Tests\Git\CiAwareGit;

use function array_map;
use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\InMemoryFileSystem;
use Infection\Git\CiAwareGit;
use Infection\Git\Git;
use Override;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function Safe\putenv;

/**
 * @phpstan-import-type log_record_array from TestLogger
 */
#[CoversClass(CiAwareGit::class)]
#[WithEnvironmentVariable('GITHUB_ACTIONS')]
#[WithEnvironmentVariable('GITHUB_EVENT_PATH')]
#[WithEnvironmentVariable('GITLAB_CI')]
#[WithEnvironmentVariable('CI_MERGE_REQUEST_DIFF_BASE_SHA')]
final class CiAwareGitTest extends TestCase
{
    private const string FALLBACK_BASE = 'fallback-base';

    private Git&MockObject $decoratedGit;

    private InMemoryFileSystem $fileSystem;

    private TestLogger $logger;

    private CiAwareGit $git;

    #[Override]
    protected function setUp(): void
    {
        $this->decoratedGit = $this->createMock(Git::class);
        $this->decoratedGit
            ->method('getDefaultBase')
            ->willReturn(self::FALLBACK_BASE)
        ;

        $this->fileSystem = new InMemoryFileSystem();
        $this->logger = new TestLogger();
        $this->git = new CiAwareGit(
            $this->decoratedGit,
            $this->fileSystem,
            $this->logger,
        );
    }

    #[AllowMockObjectsWithoutExpectations]
    #[DataProvider('baseProvider')]
    public function test_it_resolves_the_default_base(Scenario $scenario): void
    {
        $this->setupEnvironment($scenario->environment);

        if ($scenario->event !== null) {
            $this->fileSystem->dumpFile(
                '/github/event.json',
                $scenario->event,
            );
        }

        $actual = $this->git->getDefaultBase();
        $records = $this->getRecordsWithoutExceptions();

        $this->assertSame($scenario->expected, $actual);
        $this->assertSame($scenario->expectedRecords, $records);
    }

    public static function baseProvider(): iterable
    {
        $baseScenario = new Scenario(
            environment: [],
            event: null,
            expected: self::FALLBACK_BASE,
            expectedRecords: [],
        );

        yield 'outside CI' => [
            $baseScenario,
        ];

        yield 'CI variables are ignored outside their provider' => [
            $baseScenario
                ->withEnvironment([
                    'CI_MERGE_REQUEST_DIFF_BASE_SHA' => 'gitlab-base',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ]),
        ];

        yield 'GitLab merge request' => [
            $baseScenario
                ->withEnvironment([
                    'GITLAB_CI' => 'true',
                    'CI_MERGE_REQUEST_DIFF_BASE_SHA' => 'gitlab-base',
                ])
                ->withExpected('gitlab-base'),
        ];

        yield 'GitLab branch pipeline' => [
            $baseScenario
                ->withEnvironment([
                    'GITLAB_CI' => 'true',
                ]),
        ];

        yield 'empty GitLab diff base' => [
            $baseScenario
                ->withEnvironment([
                    'GITLAB_CI' => 'true',
                    'CI_MERGE_REQUEST_DIFF_BASE_SHA' => '',
                ]),
        ];

        yield 'GitHub event path is missing' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                ]),
        ];

        yield 'GitHub event path is empty' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '',
                ]),
        ];

        yield 'GitHub pull request uses its immediate target' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{"pull_request":{"base":{"sha":"immediate-base"},"stack":{"base":{"sha":"stack-base"}}}}')
                ->withExpected('immediate-base'),
        ];

        yield 'push event' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{}'),
        ];

        yield 'missing base SHA' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{"pull_request":{"base":{"ref":"main"}}}'),
        ];

        yield 'empty base SHA' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{"pull_request":{"base":{"sha":""}}}'),
        ];

        yield 'non-string base SHA' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{"pull_request":{"base":{"sha":123}}}'),
        ];

        yield 'non-object event' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('null'),
        ];

        yield 'corrupt JSON payload' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withEvent('{')
                ->withExpectedRecords([
                    [
                        'level' => LogLevel::INFO,
                        'message' => 'Could not determine the GitHub pull request base from "/github/event.json". Falling back to the default Git base.',
                        'context' => [],
                    ],
                ]),
        ];

        yield 'missing event file' => [
            $baseScenario
                ->withEnvironment([
                    'GITHUB_ACTIONS' => 'true',
                    'GITHUB_EVENT_PATH' => '/github/event.json',
                ])
                ->withExpectedRecords([
                    [
                        'level' => LogLevel::INFO,
                        'message' => 'Could not determine the GitHub pull request base from "/github/event.json". Falling back to the default Git base.',
                        'context' => [],
                    ],
                ]),
        ];
    }

    public function test_it_delegates_changed_file_paths(): void
    {
        $expected = ['/project/src/Changed.php'];

        $this->decoratedGit
            ->expects($this->once())
            ->method('getChangedFilePaths')
            ->with(
                'M',
                'base',
                ['src'],
                '/project',
            )
            ->willReturn($expected)
        ;

        $actual = $this->git->getChangedFilePaths(
            'M',
            'base',
            ['src'],
            '/project',
        );

        $this->assertSame($expected, $actual);
    }

    public function test_it_delegates_changed_line_ranges(): void
    {
        $expected = [
            '/project/src/Changed.php' => [ChangedLinesRange::forLine(42)],
        ];

        $this->decoratedGit
            ->expects($this->once())
            ->method('getChangedLinesRangesByFilePaths')
            ->with(
                'AM',
                'base',
                ['src', 'lib'],
                '/project',
            )
            ->willReturn($expected)
        ;

        $actual = $this->git->getChangedLinesRangesByFilePaths(
            'AM',
            'base',
            ['src', 'lib'],
            '/project',
        );

        $this->assertSame($expected, $actual);
    }

    public function test_it_delegates_base_reference_resolution(): void
    {
        $expected = 'merge-base';

        $this->decoratedGit
            ->expects($this->once())
            ->method('getBaseReference')
            ->with('base')
            ->willReturn($expected)
        ;

        $actual = $this->git->getBaseReference('base');

        $this->assertSame($expected, $actual);
    }

    public function test_it_delegates_project_directory_resolution(): void
    {
        $expected = '/project';

        $this->decoratedGit
            ->expects($this->once())
            ->method('getProjectDirectory')
            ->willReturn($expected)
        ;

        $actual = $this->git->getProjectDirectory();

        $this->assertSame($expected, $actual);
    }

    /**
     * @param array<string, string> $environment
     */
    public function setupEnvironment(array $environment): void
    {
        foreach ($environment as $name => $value) {
            putenv($name . '=' . $value);
        }
    }

    /**
     * @return list<log_record_array>
     */
    private function getRecordsWithoutExceptions(): array
    {
        return array_map(
            self::removeRecordException(...),
            $this->logger->records,
        );
    }

    /**
     * @param log_record_array $record
     *
     * @return log_record_array
     */
    private static function removeRecordException(array $record): array
    {
        unset($record['context']['exception']);

        return $record;
    }
}
