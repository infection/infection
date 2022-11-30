<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage;

use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport\TestLocator;
use _HumbugBox9658796bb9f0\Later\Interfaces\Deferred;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class ProxyTrace implements Trace
{
    private ?TestLocator $tests = null;
    public function __construct(private SplFileInfo $sourceFile, private ?Deferred $lazyTestLocations = null)
    {
    }
    public function getSourceFileInfo() : SplFileInfo
    {
        return $this->sourceFile;
    }
    public function getRealPath() : string
    {
        $realPath = $this->sourceFile->getRealPath();
        Assert::string($realPath);
        return $realPath;
    }
    public function getRelativePathname() : string
    {
        return $this->sourceFile->getRelativePathname();
    }
    public function hasTests() : bool
    {
        if ($this->lazyTestLocations === null) {
            return \false;
        }
        return $this->getTestLocator()->hasTests();
    }
    public function getTests() : ?TestLocations
    {
        if ($this->lazyTestLocations !== null) {
            return $this->lazyTestLocations->get();
        }
        return null;
    }
    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature) : iterable
    {
        if ($this->lazyTestLocations === null) {
            return [];
        }
        return $this->getTestLocator()->getAllTestsForMutation($lineRange, $isOnFunctionSignature);
    }
    private function getTestLocator() : TestLocator
    {
        if ($this->tests !== null) {
            return $this->tests;
        }
        $testLocations = $this->getTests();
        Assert::notNull($testLocations);
        $this->tests = new TestLocator($testLocations);
        return $this->tests;
    }
}
