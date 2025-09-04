<?php

declare(strict_types=1);

namespace Infection\SourceCollection;

use Infection\Configuration\Entry\GitOptions;
use Infection\Git\Git;
use Infection\Process\ShellCommandLineExecutor;
use Infection\Tracing\Tracer;

final readonly class SourceCollectorFactory
{
    public function __construct(
        private Git $git,
        private ShellCommandLineExecutor $shellCommandLineExecutor,
        private Tracer $tracer,
    ) {
    }

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedDirectoriesOrFiles
     * @param non-empty-string|GitOptions|null $sourceFilter E.g. "src/Service/Mailer.php", "Mailer.php", "src/Service/", "Mailer.php,Sender.php", etc.
     */
    public function create(
        array                  $sourceDirectories,
        array                  $excludedDirectoriesOrFiles,
        string|GitOptions|null $sourceFilter,
        bool                   $mutateOnlyCoveredCode,
    ): SourceCollector
    {
        $collector = self::createBasicCollector(
            $sourceDirectories,
            $excludedDirectoriesOrFiles,
            $sourceFilter,
        );

        return $mutateOnlyCoveredCode
            ? new CoveredSourceCollector(
                $collector,
                $this->tracer,
            )
            : $collector;
    }

    /**
     * @param string[] $sourceDirectories
     * @param string[] $excludedDirectoriesOrFiles
     * @param non-empty-string|GitOptions|null $sourceFilter E.g. "src/Service/Mailer.php", "Mailer.php", "src/Service/", "Mailer.php,Sender.php", etc.
     */
    private function createBasicCollector(
        array                  $sourceDirectories,
        array                  $excludedDirectoriesOrFiles,
        string|GitOptions|null $sourceFilter,
    ): SourceCollector
    {
        if ($sourceFilter instanceof GitOptions) {
            return $this->createGitDiffSourceCollector(
                $sourceFilter,
                $sourceDirectories,
                $excludedDirectoriesOrFiles,    // TODO
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
        GitOptions $options,
        array $sourceDirectories,
    ): GitDiffSourceCollector
    {
        $baseBranch = $gitDiffBase ?? $this->git->getDefaultBase();
        $filter = $options->isForGitDiffLines
            ? $this->git->getDefaultBaseFilter()
            : $options->gitDiffFilter;

        return new GitDiffSourceCollector(
            $this->shellCommandLineExecutor,
            $baseBranch,
            $filter,
            $sourceDirectories,
        );
    }
}
