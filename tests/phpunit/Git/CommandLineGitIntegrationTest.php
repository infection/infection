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

namespace Infection\Tests\Git;

use function explode;
use function implode;
use Infection\Framework\Str;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Process\ShellCommandLineExecutor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * This is an integration to smoke test that the adapter works. More accurate
 * and detailed tests can be found in the unit test CommandLineGitTest.
 */
#[Group('integration')]
#[CoversClass(CommandLineGit::class)]
final class CommandLineGitIntegrationTest extends TestCase
{
    // https://github.com/infection/infection/commit/40d08afda22d5fe6d0d87ffb95fd609dcb01992a
    // At minimum we will have the following files in the entire output:
    // - src/Git/CommandLineGit.php
    // - src/Git/Git.php
    // - src/Process/ShellCommandLineExecutor.php
    // - tests/phpunit/AutoReview/ProjectCode/ProjectCodeProvider.php
    // - tests/phpunit/Differ/FilesDiffChangedLinesTest.php
    // - tests/phpunit/Git/CommandLineGitIntegrationTest.php
    // - tests/phpunit/Git/CommandLineGitTest.php
    // - tests/phpunit/Process/ShellCommandLineExecutorTest.php
    private const COMMIT_REFERENCE = '40d08afda22d5fe6d0d87ffb95fd609dcb01992a';

    private const BAD_COMMIT_REFERENCE = '40d08afda22d5fe6d0d87ffb95fd609dcb01992a40d08afda22d5fe6d0d87ffb95fd609dcb01992a';

    private static bool $commitReferenceExists;

    private Git $git;

    public static function setUpBeforeClass(): void
    {
        if (!self::checkIfCommitReferenceExists()) {
            self::markTestSkipped('Commit reference not found. It may require more history.');
        }
    }

    protected function setUp(): void
    {
        $this->git = new CommandLineGit(
            new ShellCommandLineExecutor(),
        );
    }

    public function test_it_gets_the_relative_paths_of_the_changed_files_as_a_string(): void
    {
        $this->skipIfCommitReferenceIsNotAvailable();

        $output = $this->git->getChangedFileRelativePaths(
            'AM',
            self::COMMIT_REFERENCE,
            ['src/Git'],
        );
        $paths = explode(',', $output);

        $expectedFiles = [
            'src/Git/CommandLineGit.php',
            'src/Git/Git.php',
        ];

        foreach ($expectedFiles as $expectedFile) {
            $this->assertContains(
                $expectedFile,
                $paths,
                implode("\n", $paths),
            );
        }
    }

    public function test_it_fails_at_getting_the_relative_paths_of_the_changed_files_if_getting_the_merge_base_failed_unexpectedly(): void
    {
        $badCommitReference = self::BAD_COMMIT_REFERENCE;

        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessage('Exit Code:');
        $this->expectExceptionMessage('Working directory:');
        $this->expectExceptionMessage(
            Str::toSystemLineEndings(
                <<<EOF
                    Output:
                    ================


                    Error Output:
                    ================
                    fatal: bad revision '{$badCommitReference}'
                    EOF,
            ),
        );

        $this->git->getChangedFileRelativePaths(
            'AM',
            // This cannot be a correct revision.
            $badCommitReference,
            ['src'],
        );
    }

    public function test_it_get_the_changed_lines(): void
    {
        $this->skipIfCommitReferenceIsNotAvailable();

        $actual = $this->git->getChangedLinesRangesByFileRelativePaths(
            'AM',
            self::COMMIT_REFERENCE,
            ['src', 'tests'],
        );

        $this->assertArrayHasKey('src/Git/Git.php', $actual);
        $this->assertArrayHasKey('tests/phpunit/Git/CommandLineGitTest.php', $actual);
    }

    public function test_it_get_the_changed_lines_excluding_the_files_that_are_not_part_of_the_source_directories(): void
    {
        $this->skipIfCommitReferenceIsNotAvailable();

        $actual = $this->git->getChangedLinesRangesByFileRelativePaths(
            'AM',
            self::COMMIT_REFERENCE,
            ['src'],
        );

        $this->assertArrayHasKey('src/Git/Git.php', $actual);
        $this->assertArrayNotHasKey('tests/phpunit/Git/CommandLineGitTest.php', $actual);
    }

    public function test_it_fails_at_getting_the_modified_lines_if_getting_the_merge_base_failed_unexpectedly(): void
    {
        $badCommitReference = self::BAD_COMMIT_REFERENCE;

        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessage('Exit Code:');
        $this->expectExceptionMessage('Working directory:');
        $this->expectExceptionMessage(
            Str::toSystemLineEndings(
                <<<EOF
                    Output:
                    ================


                    Error Output:
                    ================
                    fatal: bad revision '{$badCommitReference}'
                    EOF,
            ),
        );

        $this->git->getChangedLinesRangesByFileRelativePaths(
            'AM',
            $badCommitReference,
            ['src'],
        );
    }

    public function test_it_can_get_this_project_default_git_base(): void
    {
        $actual = $this->git->getDefaultBase();

        $this->assertContains($actual, [
            'origin/master',
            'refs/remotes/origin/master',
        ]);
    }

    public function test_it_can_refine_the_base_used(): void
    {
        $originalBase = $this->git->getDefaultBase();

        $refinedBase = $this->git->getBaseReference($originalBase);

        $this->assertNotSame($originalBase, $refinedBase);
    }

    private function skipIfCommitReferenceIsNotAvailable(): void
    {
        if (!isset(self::$commitReferenceExists)) {
            self::$commitReferenceExists = self::checkIfCommitReferenceExists();
        }

        if (!self::$commitReferenceExists) {
            $this->markTestSkipped('Commit reference not found. It may require more history.');
        }
    }

    private static function checkIfCommitReferenceExists(): bool
    {
        try {
            (new ShellCommandLineExecutor())->execute([
                'git',
                'cat-file',
                '-e',
                self::COMMIT_REFERENCE,
            ]);

            return true;
        } catch (ProcessFailedException) {
            return false;
        }
    }
}
