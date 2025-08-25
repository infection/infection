<?php

declare(strict_types=1);

namespace Infection\SourceCollection;

use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Git\Git;
use Infection\Process\ShellCommandLineExecutor;
use Symfony\Component\Finder\SplFileInfo;

final readonly class SourceCollectorFactory
{
    public function __construct(
        private Git $git,
        private GitDiffFileProvider $gitDiffFileProvider,
        private ShellCommandLineExecutor $shellCommandLineExecutor,
    ) {

    }

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedDirectoriesOrFiles
     * @param string $filter E.g. "src/Service/Mailer.php", "Mailer.php", "src/Service/", "Mailer.php,Sender.php", etc.
     */
    public function create(
        array $sourceDirectories,
        array $excludedDirectoriesOrFiles,
        string $filter,
        ?string $gitDiffFilter,
        bool $isForGitDiffLines,
        ?string $gitDiffBase,
        bool $mutateOnlyCoveredCode,
    ): SourceCollector
    {
        if ($gitDiffFilter !== null && $isForGitDiffLines) {
            return $this->createGitDiffSourceCollector(
                $gitDiffFilter,
                $gitDiffBase,
                $sourceDirectories,
                $excludedDirectoriesOrFiles,    // TODO
            );
        }

        if ($mutateOnlyCoveredCode) {
            return new CoveredSourceCollector(
                $sourceDirectories,
                $excludedDirectoriesOrFiles,
            );
        }

        // TODO: apply $filter here
        return new SchemaSourceCollector(
            $sourceDirectories,
            $excludedDirectoriesOrFiles,
        );
    }

    /**
     * @param string[] $sourceDirectories
     */
    private function createGitDiffSourceCollector(
        ?string $gitDiffFilter,
        ?string $gitDiffBase,
        array $sourceDirectories,
    ): GitDiffSourceCollector
    {
        $baseBranch = $gitDiffBase ?? $this->git->getDefaultBase();
        $gitDiffFilter = $gitDiffFilter ?? $this->git->getDefaultBaseFilter();

        return new GitDiffSourceCollector(
            $this->shellCommandLineExecutor,
            $baseBranch,
            $gitDiffFilter,
            $sourceDirectories,
        );
    }
}
