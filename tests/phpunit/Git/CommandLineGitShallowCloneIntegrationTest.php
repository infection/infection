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

use Infection\Differ\ChangedLinesRange;
use Infection\Git\CommandLineGit;
use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\GitRepositorySetup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\Test\TestLogger;
use function Safe\chdir;

/**
 * Kept separate from CommandLineGitIntegrationTest because these scenarios need
 * more setup: a repository with diverging branches and a shallow clone whose
 * history each test fetches differently.
 */
#[Group('integration')]
#[CoversClass(CommandLineGit::class)]
final class CommandLineGitShallowCloneIntegrationTest extends FileSystemTestCase
{
    use GitRepositorySetup;

    private TestLogger $logger;

    private CommandLineGit $git;

    /**
     * @var non-empty-string
     */
    private string $base;

    /**
     * @var non-empty-string
     */
    private string $mergeBase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestLogger();
        $this->git = new CommandLineGit(
            new SymfonyProcessShellCommandRunner(),
            $this->logger,
        );

        $this->setUpShallowClone();
    }

    public function test_it_includes_unrelated_target_changes_when_shallow_history_hides_the_merge_base(): void
    {
        self::runGit(
            $this->tmp . '/clone',
            [
                'fetch',
                '--quiet',
                '--depth=1',
                'origin',
                $this->base,
            ],
        );

        $base = $this->git->getBaseReference($this->base);

        $this->assertSame(
            $this->base,
            $base,
        );
        $this->assertTrue(
            $this->logger->hasInfoThatContains('Could not find a common ancestor commit'),
        );

        $actual = $this->git->getChangedLinesRangesByFilePaths(
            'AM',
            $base,
            ['src'],
            $this->tmp . '/clone',
        );

        $this->assertEquals(
            [
                $this->tmp . '/clone/src/Order.php' => [ChangedLinesRange::forLine(1)],
                $this->tmp . '/clone/src/Tax.php' => [ChangedLinesRange::forLine(1)],
            ],
            $actual,
        );
    }

    public function test_it_selects_only_the_pull_request_changes_after_fetching_both_histories(): void
    {
        self::runGit(
            $this->tmp . '/clone',
            [
                'fetch',
                '--quiet',
                '--no-tags',
                '--unshallow',
                'origin',
                'feature',
                'main',
            ],
        );

        $base = $this->git->getBaseReference($this->base);

        $this->assertSame(
            $this->mergeBase,
            $base,
        );
        $this->assertSame(
            [],
            $this->logger->records,
        );

        $actual = $this->git->getChangedLinesRangesByFilePaths(
            'AM',
            $base,
            ['src'],
            $this->tmp . '/clone',
        );

        $this->assertEquals(
            [
                $this->tmp . '/clone/src/Order.php' => [ChangedLinesRange::forLine(1)],
            ],
            $actual,
        );
    }

    /**
     * Create branches that each change a different file after a common ancestor:
     *
     * main:    A --- T (Tax.php)
     *           \
     * feature:   H (Order.php)
     *
     * The clone contains only the feature tip (H), without the target tip (T) or
     * their common ancestor (A). Each test controls how much history is fetched
     * to show whether the diff includes only the pull request change or also
     * the unrelated target branch change.
     */
    private function setUpShallowClone(): void
    {
        $repository = $this->tmp . '/remote';

        self::initializeGitRepository($repository);

        FS::dumpFile($repository . '/src/Order.php', '<?php return 1;');
        FS::dumpFile($repository . '/src/Tax.php', '<?php return 1.20;');

        $this->mergeBase = self::commitGitChanges($repository, 'Common ancestor');

        self::runGit(
            $repository,
            [
                'branch',
                'feature',
            ],
        );

        FS::dumpFile($repository . '/src/Tax.php', '<?php return 1.21;');

        $this->base = self::commitGitChanges($repository, 'Target branch change');

        self::runGit(
            $repository,
            [
                'checkout',
                '--quiet',
                'feature',
            ],
        );

        FS::dumpFile($repository . '/src/Order.php', '<?php return 2;');
        self::commitGitChanges($repository, 'Pull request change');

        self::runGit(
            $this->tmp,
            [
                'clone',
                '--quiet',
                '--no-local',
                '--depth=1',
                '--branch=feature',
                $repository,
                $this->tmp . '/clone',
            ],
        );
        chdir($this->tmp . '/clone');
    }
}
