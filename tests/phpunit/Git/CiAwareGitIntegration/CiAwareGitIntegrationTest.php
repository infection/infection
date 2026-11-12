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
#[WithEnvironmentVariable('GITHUB_BASE_REF')]
#[WithEnvironmentVariable('GITHUB_EVENT_PATH')]
#[WithEnvironmentVariable('GITLAB_CI')]
#[WithEnvironmentVariable('CI_MERGE_REQUEST_DIFF_BASE_SHA')]
#[WithEnvironmentVariable('CI_MERGE_REQUEST_EVENT_TYPE')]
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
            Scenario::REPOSITORY_DEFAULT_BASE_OUTSIDE_CI->name => $this->setUpRepositoryDefaultBase(...),
            Scenario::GITHUB_PULL_REQUEST_TARGETING_NON_DEFAULT_BRANCH->name => $this->setUpGitHubPullRequest(...),
            Scenario::GITHUB_PULL_REQUEST_MERGE_CHECKOUT_WITH_DIVERGED_TARGET_BRANCH->name => $this->setUpGitHubPullRequestMergeCheckout(...),
            Scenario::GITHUB_STACKED_PULL_REQUEST_WITH_DISTINCT_STACK_BASE->name => $this->setUpGitHubStackedPullRequest(...),
            Scenario::GITHUB_MERGE_GROUP_TARGETING_NON_DEFAULT_BRANCH->name => $this->setUpGitHubMergeGroup(...),
            Scenario::GITLAB_DETACHED_MERGE_REQUEST_WITH_DIVERGED_TARGET_BRANCH->name => $this->setUpGitLabMergeRequest(...),
            Scenario::GITLAB_MERGED_RESULT_WITH_DIVERGED_TARGET_BRANCH->name => $this->setUpGitLabMergedResult(...),
            Scenario::GITLAB_MERGE_TRAIN_WITH_NEWER_TARGET_BRANCH_CHANGES->name => $this->setUpGitLabMergeTrain(...),
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
        yield 'repository default base outside CI' => [
            Scenario::REPOSITORY_DEFAULT_BASE_OUTSIDE_CI,
            [
                'src/Feature.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitHub pull request targeting a non-default branch' => [
            Scenario::GITHUB_PULL_REQUEST_TARGETING_NON_DEFAULT_BRANCH,
            [
                'src/PullRequest.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitHub pull request merge checkout excludes newer target changes' => [
            Scenario::GITHUB_PULL_REQUEST_MERGE_CHECKOUT_WITH_DIVERGED_TARGET_BRANCH,
            [
                'src/PullRequest.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitHub stacked pull request uses its immediate target' => [
            Scenario::GITHUB_STACKED_PULL_REQUEST_WITH_DISTINCT_STACK_BASE,
            [
                'src/PullRequest2.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitHub merge group targeting a non-default branch' => [
            Scenario::GITHUB_MERGE_GROUP_TARGETING_NON_DEFAULT_BRANCH,
            [
                'src/PullRequest1.php' => [ChangedLinesRange::forLine(1)],
                'src/PullRequest2.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitLab detached merge request excludes changes from its diverged target branch' => [
            Scenario::GITLAB_DETACHED_MERGE_REQUEST_WITH_DIVERGED_TARGET_BRANCH,
            [
                'src/MergeRequest.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitLab merged result includes newer target changes since the MR diff base' => [
            Scenario::GITLAB_MERGED_RESULT_WITH_DIVERGED_TARGET_BRANCH,
            [
                'src/MergeRequest.php' => [ChangedLinesRange::forLine(1)],
                'src/Target.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];

        yield 'GitLab merge train includes earlier merge requests and newer target changes' => [
            Scenario::GITLAB_MERGE_TRAIN_WITH_NEWER_TARGET_BRANCH_CHANGES,
            [
                'src/MergeRequestA.php' => [ChangedLinesRange::forLine(1)],
                'src/MergeRequestB.php' => [ChangedLinesRange::forLine(1)],
                'src/Target.php' => [ChangedLinesRange::forLine(1)],
            ],
        ];
    }

    /**
     * Outside CI, origin/HEAD identifies the repository's default branch.
     *
     * D-----F
     *
     * D: main, origin/main, origin/HEAD (default comparison base)
     * F: feature, HEAD; adds Feature.php
     *
     * Comparing D to F selects only Feature.php.
     *
     * @see https://git-scm.com/docs/git-remote#Documentation/git-remote.txt-set-head
     */
    private function setUpRepositoryDefaultBase(): void
    {
        // Create main and point origin/HEAD at its remote reference (D).
        $this->setUpRepositoryDefaultBranch();

        // Check out a feature branch with one change after the default base (F).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'feature']);
        FS::dumpFile($this->tmp . '/src/Feature.php', '<?php return 1;');
        self::commitGitChanges($this->tmp, 'Feature branch change');
    }

    /**
     * Simulates a pull_request workflow that explicitly checks out the PR head.
     * Its target branch is release, independently of the default branch main.
     *
     * D-----B-----P
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * B: release; adds Release.php (pull_request.base.sha)
     * P: pull-request; adds PullRequest.php (HEAD, pull_request.head.sha)
     *
     * Comparing B to P selects only PullRequest.php. Falling back to D also
     * selects Release.php, which predates the pull request.
     *
     * @see https://github.com/actions/checkout#checkout-pull-request-head-commit-instead-of-merge-commit
     * @see https://docs.github.com/en/webhooks/webhook-events-and-payloads#pull_request
     */
    private function setUpGitHubPullRequest(): void
    {
        // Create the repository's default branch (D).
        $this->setUpRepositoryDefaultBranch();

        // Create the pull request's target with an older release change (B).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        $base = self::commitGitChanges($this->tmp, 'Existing release change');

        // Create the pull request and explicitly check out its head for CI (P).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pull-request']);
        FS::dumpFile($this->tmp . '/src/PullRequest.php', '<?php return 2;');
        $head = self::commitGitChanges($this->tmp, 'Pull request change');
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', $head]);

        // Supply the recorded target and source commits from the PR event.
        $this->setGitHubEvent([
            'action' => 'synchronize',
            'pull_request' => [
                'base' => ['ref' => 'release', 'sha' => $base],
                'head' => ['ref' => 'pull-request', 'sha' => $head],
            ],
        ]);
    }

    /**
     * Simulates the default pull_request checkout of a temporary merge commit,
     * with newer target changes already included in the checkout.
     *
     * D-----B-----T-----C
     *        \        /
     *         P------'
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * B: adds Release.php and Target.php before the PR branches off
     * T: release, origin/release; modifies Target.php (pull_request.base.sha)
     * P: pull-request; adds PullRequest.php (pull_request.head.sha)
     * C: refs/pull/1/merge; combines T and P (HEAD)
     *
     * Comparing T to C selects only PullRequest.php. Comparing B to C would
     * also select Target.php, and falling back to D selects Release.php too.
     *
     * @see https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows#pull_request
     */
    private function setUpGitHubPullRequestMergeCheckout(): void
    {
        // Create the default branch and the PR's original target baseline (D, B).
        $this->setUpRepositoryDefaultBranch();
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 1;');
        self::commitGitChanges($this->tmp, 'Existing release change');

        // Create the PR before its target branch advances (P).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pull-request']);
        FS::dumpFile($this->tmp . '/src/PullRequest.php', '<?php return 2;');
        $head = self::commitGitChanges($this->tmp, 'Pull request change');

        // Modify an existing file on the target after the branches diverge (T).
        self::runGit($this->tmp, ['checkout', '--quiet', 'release']);
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 3;');
        $target = self::commitGitChanges($this->tmp, 'Newer target branch change');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/release', $target]);

        // Construct GitHub's temporary merge ref and check it out detached (C).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pull-request-merge']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Pull request merge result', 'pull-request']);
        $merge = self::runGit($this->tmp, ['rev-parse', 'HEAD']);
        self::runGit($this->tmp, ['update-ref', 'refs/pull/1/merge', $merge]);
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', 'refs/pull/1/merge']);

        // The event records the PR's target and source, independently of HEAD.
        $this->setGitHubEvent([
            'action' => 'synchronize',
            'pull_request' => [
                'base' => ['ref' => 'release', 'sha' => $target],
                'head' => ['ref' => 'pull-request', 'sha' => $head],
            ],
        ]);
    }

    /**
     * Simulates the upper pull request in a two-PR stack. The job explicitly
     * checks out its head so that the diff describes that layer's own changes.
     *
     * D-----P1-----P2
     *
     * D: main, origin/main, origin/HEAD (pull_request.stack.base.sha)
     * P1: pr-1; adds PullRequest1.php (pull_request.base.sha)
     * P2: pr-2; adds PullRequest2.php (HEAD, pull_request.head.sha)
     *
     * Comparing P1 to P2 selects only PullRequest2.php. Using the stack base D
     * also selects the lower pull request's PullRequest1.php change.
     *
     * @see https://docs.github.com/en/pull-requests/how-tos/merge-and-close-pull-requests/optimizing-ci-for-stacked-pull-requests
     * @see https://github.com/actions/checkout#checkout-pull-request-head-commit-instead-of-merge-commit
     */
    private function setUpGitHubStackedPullRequest(): void
    {
        // Create the branch targeted by the entire stack (D).
        $stackBase = $this->setUpRepositoryDefaultBranch();

        // Create the lower pull request, which targets main (P1).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pr-1']);
        FS::dumpFile($this->tmp . '/src/PullRequest1.php', '<?php return 1;');
        $immediateBase = self::commitGitChanges($this->tmp, 'Pull request #1');

        // Build the upper pull request on pr-1 and check out its head (P2).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'pr-2']);
        FS::dumpFile($this->tmp . '/src/PullRequest2.php', '<?php return 2;');
        $head = self::commitGitChanges($this->tmp, 'Pull request #2');
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', $head]);

        // Distinguish the immediate PR target from the stack-wide base.
        putenv('GITHUB_BASE_REF=main');
        $this->setGitHubEvent([
            'action' => 'synchronize',
            'pull_request' => [
                'base' => ['ref' => 'pr-1', 'sha' => $immediateBase],
                'head' => ['ref' => 'pr-2', 'sha' => $head],
                'stack' => [
                    'base' => ['ref' => 'main', 'sha' => $stackBase],
                    'position' => 2,
                    'size' => 2,
                ],
            ],
        ]);
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
        // Create the default branch and its remote references (D).
        $this->setUpRepositoryDefaultBranch();

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
        $this->setGitHubEvent([
            'action' => 'checks_requested',
            'merge_group' => [
                'base_ref' => 'refs/heads/release',
                'base_sha' => $mergeGroupBase,
                'head_ref' => 'refs/heads/gh-readonly-queue/release/pr-2',
                'head_sha' => $head,
            ],
        ]);
    }

    /**
     * Simulates a detached merge request pipeline, which tests the source branch
     * without merging the target branch's newer changes into the checkout.
     *
     * D-----B-----T
     *        \
     *         M
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * B: adds Release.php and Target.php (CI_MERGE_REQUEST_DIFF_BASE_SHA)
     * T: release, origin/release; changes Target.php after the branches diverge
     * M: merge-request; adds MergeRequest.php (HEAD)
     *
     * Comparing B to M selects only MergeRequest.php. Release.php and Target.php
     * predate the MR, whose checkout retains the older Target.php contents.
     * Falling back to D includes both files; diffing directly against T instead
     * of the merge base B also incorrectly selects Target.php.
     *
     * @see https://docs.gitlab.com/ci/pipelines/merge_request_pipelines/
     * @see https://docs.gitlab.com/ci/variables/predefined_variables/#predefined-variables-for-merge-request-pipelines
     */
    private function setUpGitLabMergeRequest(): void
    {
        // Create the repository's default branch (D).
        $this->setUpRepositoryDefaultBranch();

        // Create the MR's target branch and the common ancestor of its diff (B).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 1;');
        $diffBase = self::commitGitChanges($this->tmp, 'Existing release change');

        // Create the merge request's source branch (M).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'merge-request']);
        FS::dumpFile($this->tmp . '/src/MergeRequest.php', '<?php return 2;');
        $head = self::commitGitChanges($this->tmp, 'Merge request change');

        // Modify an existing file on the target after the source branches off (T).
        self::runGit($this->tmp, ['checkout', '--quiet', 'release']);
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 3;');
        $target = self::commitGitChanges($this->tmp, 'Newer target branch change');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/release', $target]);

        // Check out only the source head and supply the detached MR metadata.
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', $head]);
        self::setGitLabEvent('detached', $diffBase);
    }

    /**
     * Simulates a merged results pipeline after the target branch advances.
     * The checkout combines the source and target, but the CI diff base still
     * identifies their original common ancestor.
     *
     * D-----B-----T-----C
     *        \        /
     *         M------'
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * B: adds Release.php and Target.php (CI_MERGE_REQUEST_DIFF_BASE_SHA)
     * T: release, origin/release; modifies Target.php after the branches diverge
     * M: merge-request; adds MergeRequest.php
     * C: refs/merge-requests/1/merge; combines T and M (HEAD)
     *
     * Comparing B to C selects MergeRequest.php and the newer Target.php change.
     * Falling back to D also selects Release.php. Comparing against T instead
     * would omit Target.php and change the meaning of the CI diff base.
     *
     * @see https://docs.gitlab.com/ci/pipelines/merged_results_pipelines/
     * @see https://docs.gitlab.com/ci/variables/predefined_variables/#predefined-variables-for-merge-request-pipelines
     */
    private function setUpGitLabMergedResult(): void
    {
        // Create the default branch and the original MR diff base (D, B).
        $this->setUpRepositoryDefaultBranch();
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 1;');
        $diffBase = self::commitGitChanges($this->tmp, 'Existing release change');

        // Create the MR before its target branch advances (M).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'merge-request']);
        FS::dumpFile($this->tmp . '/src/MergeRequest.php', '<?php return 2;');
        self::commitGitChanges($this->tmp, 'Merge request change');

        // Modify an existing file on the target after the branches diverge (T).
        self::runGit($this->tmp, ['checkout', '--quiet', 'release']);
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 3;');
        $target = self::commitGitChanges($this->tmp, 'Newer target branch change');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/release', $target]);

        // Construct GitLab's temporary merge ref and check it out detached (C).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'merged-result']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Merge request merged result', 'merge-request']);
        $merge = self::runGit($this->tmp, ['rev-parse', 'HEAD']);
        self::runGit($this->tmp, ['update-ref', 'refs/merge-requests/1/merge', $merge]);
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', 'refs/merge-requests/1/merge']);

        // The merged checkout retains the MR's original diff base.
        self::setGitLabEvent('merged_result', $diffBase);
    }

    /**
     * Simulates pipeline 2 (A + B) from GitLab's merge train example, targeting
     * release instead of the default branch. The target also advances after
     * the source branches diverge, so its newer change is in the train result.
     *
     *           A------------.
     *          /              \
     * D-------R-------T--------TA-------TAB
     *          \                       /
     *           B---------------------'
     *
     * D: main, origin/main, origin/HEAD (repository default branch)
     * R: adds Release.php (MR B's CI_MERGE_REQUEST_DIFF_BASE_SHA)
     * T: release, origin/release; adds Target.php after the MRs diverge
     * A: mr-a; adds MergeRequestA.php (first MR in the train)
     * B: mr-b; adds MergeRequestB.php (second MR in the train)
     * TA: first train result; target branch + MR A
     * TAB: second train result; target branch + MRs A and B (HEAD)
     *
     * Comparing R to TAB includes both MRs and the newer Target.php change.
     * Selecting the MR diff base does not isolate MR B from the combined
     * checkout. Falling back to D also includes the older Release.php change.
     *
     * @see https://docs.gitlab.com/ci/pipelines/merge_trains/#merge-train-workflow
     * @see https://docs.gitlab.com/ci/variables/predefined_variables/#predefined-variables-for-merge-request-pipelines
     */
    private function setUpGitLabMergeTrain(): void
    {
        // Create the repository's default branch (D).
        $this->setUpRepositoryDefaultBranch();

        // Create the target branch and the common MR diff base (R).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'release']);
        FS::dumpFile($this->tmp . '/src/Release.php', '<?php return 1;');
        $diffBase = self::commitGitChanges($this->tmp, 'Existing release change');

        // Create merge request A from the target branch (A).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'mr-a']);
        FS::dumpFile($this->tmp . '/src/MergeRequestA.php', '<?php return 2;');
        self::commitGitChanges($this->tmp, 'Merge request A');

        // Create merge request B independently from the same target branch (B).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'mr-b', 'release']);
        FS::dumpFile($this->tmp . '/src/MergeRequestB.php', '<?php return 3;');
        self::commitGitChanges($this->tmp, 'Merge request B');

        // Advance the target before constructing the train's combined results (T).
        self::runGit($this->tmp, ['checkout', '--quiet', 'release']);
        FS::dumpFile($this->tmp . '/src/Target.php', '<?php return 4;');
        $target = self::commitGitChanges($this->tmp, 'Newer target branch change');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/release', $target]);

        // Pipeline 1 tests the target branch combined with MR A (TA).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'merge-train-a']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Train result for MR A', 'mr-a']);

        // Pipeline 2 also includes MR B, while keeping MR A's changes (TAB).
        self::runGit($this->tmp, ['checkout', '--quiet', '-b', 'merge-train-b']);
        self::runGit($this->tmp, ['merge', '--quiet', '--no-ff', '--no-gpg-sign', '-m', 'Train result for MR B', 'mr-b']);
        $head = self::runGit($this->tmp, ['rev-parse', 'HEAD']);

        // Check out MR B's train result and supply its original MR diff base.
        self::runGit($this->tmp, ['checkout', '--quiet', '--detach', $head]);
        self::setGitLabEvent('merge_train', $diffBase);
    }

    /**
     * @return non-empty-string
     */
    private function setUpRepositoryDefaultBranch(): string
    {
        self::initializeGitRepository($this->tmp);
        FS::dumpFile($this->tmp . '/README.md', 'Repository default branch');
        $main = self::commitGitChanges($this->tmp, 'Main baseline');
        self::runGit($this->tmp, ['update-ref', 'refs/remotes/origin/main', $main]);
        self::runGit($this->tmp, ['symbolic-ref', 'refs/remotes/origin/HEAD', 'refs/remotes/origin/main']);

        return $main;
    }

    /**
     * @param array<string, mixed> $event
     */
    private function setGitHubEvent(array $event): void
    {
        $eventPath = $this->tmp . '/event.json';
        FS::dumpFile(
            $eventPath,
            json_encode($event),
        );
        putenv('GITHUB_ACTIONS=true');
        putenv('GITHUB_EVENT_PATH=' . $eventPath);
    }

    /**
     * @param 'detached'|'merged_result'|'merge_train' $eventType
     * @param non-empty-string $diffBase
     */
    private static function setGitLabEvent(string $eventType, string $diffBase): void
    {
        putenv('GITLAB_CI=true');
        putenv('CI_MERGE_REQUEST_EVENT_TYPE=' . $eventType);
        putenv('CI_MERGE_REQUEST_DIFF_BASE_SHA=' . $diffBase);
    }
}
