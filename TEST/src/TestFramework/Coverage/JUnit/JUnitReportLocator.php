<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use function array_map;
use function count;
use function current;
use function file_exists;
use function implode;
use _HumbugBox9658796bb9f0\Infection\FileSystem\Locator\FileNotFound;
use function iterator_to_array;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\Finder;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
class JUnitReportLocator
{
    private string $defaultJUnitPath;
    private ?string $jUnitPath = null;
    public function __construct(private string $coveragePath, string $defaultJUnitPath)
    {
        $this->defaultJUnitPath = Path::canonicalize($defaultJUnitPath);
    }
    public function locate() : string
    {
        if ($this->jUnitPath !== null) {
            return $this->jUnitPath;
        }
        if (file_exists($this->defaultJUnitPath)) {
            return $this->jUnitPath = $this->defaultJUnitPath;
        }
        if (!file_exists($this->coveragePath)) {
            throw new FileNotFound(sprintf('Could not find any file with the pattern "*.junit.xml" in "%s"', $this->coveragePath));
        }
        $files = iterator_to_array(Finder::create()->files()->in($this->coveragePath)->name('/^(.+\\.)?junit\\.xml$/i')->sortByName(), \false);
        if (count($files) > 1) {
            throw new FileNotFound(sprintf('Could not locate the JUnit file: more than one file has been found with the' . ' pattern "*.junit.xml": "%s"', implode('", "', array_map(static fn(SplFileInfo $fileInfo): string => Path::canonicalize($fileInfo->getPathname()), $files))));
        }
        $junitFileInfo = current($files);
        if ($junitFileInfo !== \false) {
            return $this->jUnitPath = Path::canonicalize($junitFileInfo->getPathname());
        }
        throw new FileNotFound(sprintf('Could not find any file with the pattern "*.junit.xml" in "%s"', $this->coveragePath));
    }
}
