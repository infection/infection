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

namespace Infection\Tests\Git\CiAwareGitIntegration;

use Closure;
use Infection\Differ\ChangedLinesRange;
use Infection\FileSystem\FileSystem;
use Infection\Git\CiAwareGit;
use Infection\Git\CommandLineGit;
use Infection\Git\Git;
use Infection\Process\SymfonyProcessShellCommandRunner;
use Infection\Tests\FileSystem\FileSystemTestCase;
use Infection\Tests\TestingUtility\FS;
use Infection\Tests\TestingUtility\GitRepositorySetup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use function Safe\json_encode;
use function Safe\putenv;
use Symfony\Component\Filesystem\Path;

/**
 * Windows is excluded because Symfony 6.4 cannot reliably remove Git's read-only
 * object files when cleaning up these repositories.
 */
#[Group('integration')]
#[CoversNothing]
#[RequiresOperatingSystem('Linux|Darwin')]
#[WithEnvironmentVariable('GITHUB_ACTIONS')]
#[WithEnvironmentVariable('GITHUB_EVENT_PATH')]
#[WithEnvironmentVariable('GITLAB_CI')]
final class CiAwareGitIntegrationTest extends FileSystemTestCase
{
    use GitRepositorySetup;

    /**
     * @var array<non-empty-string, Closure(): void>
     */
    private array $scenarioMapping;

    private Git $git;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scenarioMapping = [
            Scenario::GITHUB_MERGE_GROUP->name => $this->setUpGitHubMergeGroup(...),
        ];

        $this->git = new CiAwareGit(
            new CommandLineGit(new SymfonyProcessShellCommandRunner()),
            new FileSystem(),
        );
    }

    /**
     * @param array<string, list<ChangedLinesRange>> $expected
     */
    #[DataProvider('baseSelectionProvider')]
    public function test_it_detects_the_right_base(
        Scenario $scenario,
        array $expected,
    ): void {
        ($this->scenarioMapping[$scenario->name])();

        // This is what we do when getting the base for a GitDiffFilter. See
        // ConfigurationFactory::refineGitBase().
        $base = $this->git->getBaseReference(
            $this->git->getDefaultBase(),
        );
        $workingDirectory = $this->git->getProjectDirectory();

        $changedLines = $this->git->getChangedLinesRangesByFilePaths('AM', $base, ['src'], $workingDirectory);

        $actual = [];

        foreach ($changedLines as $path => $fileChangedLines) {
            $relativePath = Path::makeRelative($path, $this->tmp);

            $actual[$relativePath] = $fileChangedLines;
        }

        $this->assertEquals($expected, $actual);
    }

    public static function baseSelectionProvider(): iterable
    {
        yield 'automatic merge group base' => [
            Scenario::GITHUB_MERGE_GROUP,
            [
                'src/PullRequest1.php' => [ChangedLinesRange::forLine(1)],
                'src/PullRequest2.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];
    }

    /**
     * Simulates the "Successful CI" example, using release as the target branch
     * instead of main so that the merge group base differs from origin/HEAD.
     *
     * Commit history (left to right):
     *
     *           P1-----------.
     *          /              \
     * D-------B----------------G1-------G2
     *          \                       /
     *           P2--------------------'
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * B: release; adds Release.php (target branch, merge_group.base_sha)
     * P1: pr-1; adds PullRequest1.php (pull request #1)
     * P2: pr-2; adds PullRequest2.php independently (pull request #2)
     * G1: gh-readonly-queue/release/pr-1; target branch + pull request #1
     * G2: gh-readonly-queue/release/pr-2; target branch + both pull requests
     *     (HEAD, merge_group.head_sha)
     *
     * Comparing B to G2 selects both pull requests' changes.
     * Falling back to D also selects the older Release.php change.
     *
     * @see https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/configuring-pull-request-merges/managing-a-merge-queue#successful-ci
     */
    private function setUpGitHubMergeGroup(): void
    {
        putenv('GITHUB_ACTIONS=true');

        // Create the default branch and its remote references (D).
        self::initializeGitRepository($this->tmp);
        FS::dumpFile($this->tmp . '/README.md', 'Repository default branch');
        $main = self::commitGitChanges($this->tmp, 'Main baseline');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/main', $main]);
        self::runGit($this->tmp, ['symbolic-ref', 'refs/remotes/origin/HEAD', 'refs/remotes/origin/main']);

        // Create the target branch with a change predating both pull requests (B).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        $mergeGroupBase = self::commitGitChanges($this->tmp, 'Existing release change');

        // Create pull request #1 from the target branch (P1).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pr-1']);
        FS::dumpFile($this->tmp . '/src/PullRequest1.php', '<?php return 2;');
        self::commitGitChanges($this->tmp, 'Pull request #1');

        // Create pull request #2 independently from the same target branch (P2).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pr-2', 'release']);
        FS::dumpFile($this->tmp . '/src/PullRequest2.php', '<?php return 1.21;');
        self::commitGitChanges($this->tmp, 'Pull request #2');

        // Adding pull request #1 creates a temporary merge group branch (G1).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'gh-readonly-queue/release/pr-1', 'release']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Merge group for pull request #1', 'pr-1']);

        // Adding pull request #2 creates another merge group including #1 (G2).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'gh-readonly-queue/release/pr-2']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Merge group for pull request #2', 'pr-2']);
        $head = self::runGit($this->tmp, ['rev-parse', 'HEAD']);

        // Supply GitHub's event file using the actual base and queue commit SHAs.
        $event = json_encode([
            'action' => 'checks_requested',
            'merge_group' => [
                'base_ref' => 'refs/heads/release',
                'base_sha' => $mergeGroupBase,
                'head_ref' => 'refs/heads/gh-readonly-queue/release/pr-2',
                'head_sha' => $head,
            ],
        ]);
        $eventPath = $this->tmp . '/event.json';
        FS::dumpFile($eventPath, $event);
        putenv('GITHUB_EVENT_PATH=' . $eventPath);
    }
}
