<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use function array_filter;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function implode;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use _HumbugBox9658796bb9f0\Safe\Exceptions\FilesystemException;
use function _HumbugBox9658796bb9f0\Safe\file_get_contents;
use function _HumbugBox9658796bb9f0\Safe\realpath;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Path;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
use function trim;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class SourceFileInfoProvider
{
    private ?SafeDOMXPath $xPath = null;
    public function __construct(private string $coverageIndexPath, private string $coverageDir, private string $relativeCoverageFilePath, private string $projectSource)
    {
    }
    public function provideFileInfo() : SplFileInfo
    {
        return $this->retrieveSourceFileInfo($this->provideXPath());
    }
    public function provideXPath() : SafeDOMXPath
    {
        if ($this->xPath !== null) {
            return $this->xPath;
        }
        $coverageFile = $this->coverageDir . '/' . $this->relativeCoverageFilePath;
        if (!file_exists($coverageFile)) {
            throw new InvalidCoverage(sprintf('Could not find the XML coverage file "%s" listed in "%s". Make sure the ' . 'coverage used is up to date', $coverageFile, $this->coverageIndexPath));
        }
        return $this->xPath = XPathFactory::createXPath(file_get_contents($coverageFile));
    }
    private function retrieveSourceFileInfo(SafeDOMXPath $xPath) : SplFileInfo
    {
        $fileNode = $xPath->query('/phpunit/file')[0];
        Assert::notNull($fileNode);
        $fileName = $fileNode->getAttribute('name');
        $relativeFilePath = $fileNode->getAttribute('path');
        if ($relativeFilePath === '') {
            $relativeFilePath = str_replace(sprintf('%s.xml', $fileName), '', $this->relativeCoverageFilePath);
        }
        $path = implode('/', array_filter([$this->projectSource, trim($relativeFilePath, '/'), $fileName]));
        try {
            $realPath = realpath($path);
        } catch (FilesystemException) {
            $coverageFilePath = Path::canonicalize($this->coverageDir . DIRECTORY_SEPARATOR . $this->relativeCoverageFilePath);
            throw new InvalidCoverage(sprintf('Could not find the source file "%s" referred by "%s". Make sure the ' . 'coverage used is up to date', $path, $coverageFilePath));
        }
        return new SplFileInfo($realPath, $relativeFilePath, $path);
    }
}
