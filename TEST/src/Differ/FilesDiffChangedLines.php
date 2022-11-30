<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Differ;

use _HumbugBox9658796bb9f0\Infection\Logger\GitHub\GitDiffFileProvider;
class FilesDiffChangedLines
{
    private ?array $memoizedFilesChangedLinesMap = null;
    public function __construct(private DiffChangedLinesParser $diffChangedLinesParser, private GitDiffFileProvider $diffFileProvider)
    {
    }
    public function contains(string $fileRealPath, int $mutationStartLine, int $mutationEndLine, ?string $gitDiffBase) : bool
    {
        $map = $this->memoizedFilesChangedLinesMap ?? ($this->memoizedFilesChangedLinesMap = $this->diffChangedLinesParser->parse($this->diffFileProvider->provideWithLines($gitDiffBase ?? GitDiffFileProvider::DEFAULT_BASE)));
        foreach ($map[$fileRealPath] ?? [] as $changedLinesRange) {
            if ($mutationEndLine >= $changedLinesRange->getStartLine() && $mutationStartLine <= $changedLinesRange->getEndLine()) {
                return \true;
            }
        }
        return \false;
    }
}
