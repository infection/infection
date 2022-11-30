<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger\GitHub;

use function array_filter;
use function array_merge;
use function explode;
use function implode;
use _HumbugBox9658796bb9f0\Infection\Process\ShellCommandLineExecutor;
use const PHP_EOL;
use function _HumbugBox9658796bb9f0\Safe\preg_match;
use _HumbugBox9658796bb9f0\Symfony\Component\Process\Exception\ProcessFailedException;
class GitDiffFileProvider
{
    public const DEFAULT_BASE = 'origin/master';
    public function __construct(private ShellCommandLineExecutor $shellCommandLineExecutor)
    {
    }
    public function provide(string $gitDiffFilter, string $gitDiffBase, array $sourceDirectories) : string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);
        $filter = $this->shellCommandLineExecutor->execute(array_merge(['git', 'diff', $referenceCommit, '--diff-filter', $gitDiffFilter, '--name-only', '--'], $sourceDirectories));
        if ($filter === '') {
            throw NoFilesInDiffToMutate::create();
        }
        return implode(',', explode(PHP_EOL, $filter));
    }
    public function provideWithLines(string $gitDiffBase) : string
    {
        $referenceCommit = $this->findReferenceCommit($gitDiffBase);
        $filter = $this->shellCommandLineExecutor->execute(['git', 'diff', $referenceCommit, '--unified=0', '--diff-filter=AM']);
        $lines = explode(PHP_EOL, $filter);
        $lines = array_filter($lines, static function ($line) : bool {
            return preg_match('/^(\\+|-|index)/', $line) === 0;
        });
        return implode(PHP_EOL, $lines);
    }
    private function findReferenceCommit(string $gitDiffBase) : string
    {
        try {
            $comparisonCommit = $this->shellCommandLineExecutor->execute(['git', 'merge-base', $gitDiffBase, 'HEAD']);
        } catch (ProcessFailedException) {
            $comparisonCommit = $gitDiffBase;
        }
        return $comparisonCommit;
    }
}
