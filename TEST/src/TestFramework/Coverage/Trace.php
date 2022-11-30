<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use _HumbugBox9658796bb9f0\Infection\FileSystem\SourceFileFilter;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
interface Trace
{
    public function getSourceFileInfo() : SplFileInfo;
    public function getRealPath() : string;
    public function getRelativePathname() : string;
    public function hasTests() : bool;
    public function getTests() : ?TestLocations;
    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature) : iterable;
}
