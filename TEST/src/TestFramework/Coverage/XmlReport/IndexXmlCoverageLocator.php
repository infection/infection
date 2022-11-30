<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

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
class IndexXmlCoverageLocator
{
    private string $defaultIndexPath;
    private ?string $indexPath = null;
    public function __construct(private string $coveragePath)
    {
        $this->defaultIndexPath = Path::canonicalize($coveragePath . '/coverage-xml/index.xml');
    }
    public function locate() : string
    {
        if ($this->indexPath !== null) {
            return $this->indexPath;
        }
        if (file_exists($this->defaultIndexPath)) {
            return $this->indexPath = $this->defaultIndexPath;
        }
        if (!file_exists($this->coveragePath)) {
            throw new FileNotFound(sprintf('Could not find any "index.xml" file in "%s"', $this->coveragePath));
        }
        $files = iterator_to_array(Finder::create()->files()->in($this->coveragePath)->name('/^index\\.xml$/i')->sortByName(), \false);
        if (count($files) > 1) {
            throw new FileNotFound(sprintf('Could not locate the XML coverage index file. More than one file has been ' . 'found: "%s"', implode('", "', array_map(static fn(SplFileInfo $fileInfo): string => Path::canonicalize($fileInfo->getPathname()), $files))));
        }
        $indexFileInfo = current($files);
        if ($indexFileInfo !== \false) {
            return $this->indexPath = Path::canonicalize($indexFileInfo->getPathname());
        }
        throw new FileNotFound(sprintf('Could not find any "index.xml" file in "%s"', $this->coveragePath));
    }
}
