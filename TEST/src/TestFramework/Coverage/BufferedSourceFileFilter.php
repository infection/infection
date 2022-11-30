<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use function array_key_exists;
use _HumbugBox9658796bb9f0\Infection\FileSystem\FileFilter;
use _HumbugBox9658796bb9f0\Infection\FileSystem\SourceFileFilter;
use function _HumbugBox9658796bb9f0\Pipeline\take;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class BufferedSourceFileFilter implements FileFilter
{
    private array $sourceFiles = [];
    public function __construct(private FileFilter $filter, iterable $sourceFiles)
    {
        foreach ($sourceFiles as $sourceFile) {
            $this->sourceFiles[(string) $sourceFile->getRealPath()] = $sourceFile;
        }
    }
    public function filter(iterable $input) : iterable
    {
        return take($this->filter->filter($input))->filter(function (Trace $trace) : bool {
            $traceRealPath = $trace->getSourceFileInfo()->getRealPath();
            Assert::string($traceRealPath);
            if (array_key_exists($traceRealPath, $this->sourceFiles)) {
                unset($this->sourceFiles[$traceRealPath]);
                return \true;
            }
            return \false;
        });
    }
    public function getUnseenInCoverageReportFiles() : iterable
    {
        $result = $this->filter->filter($this->sourceFiles);
        return $result;
    }
}
