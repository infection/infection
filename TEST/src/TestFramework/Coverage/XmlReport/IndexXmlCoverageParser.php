<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use DOMElement;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
class IndexXmlCoverageParser
{
    private bool $isForGitDiffLines;
    public function __construct(bool $isForGitDiffLines)
    {
        $this->isForGitDiffLines = $isForGitDiffLines;
    }
    public function parse(string $coverageIndexPath, string $xmlIndexCoverageContent, string $coverageBasePath) : iterable
    {
        $xPath = XPathFactory::createXPath($xmlIndexCoverageContent);
        self::assertHasExecutedLines($xPath, $this->isForGitDiffLines);
        return $this->parseNodes($coverageIndexPath, $coverageBasePath, $xPath);
    }
    private function parseNodes(string $coverageIndexPath, string $coverageBasePath, SafeDOMXPath $xPath) : iterable
    {
        $projectSource = self::getProjectSource($xPath);
        foreach ($xPath->query('//file') as $node) {
            $relativeCoverageFilePath = $node->getAttribute('href');
            (yield new SourceFileInfoProvider($coverageIndexPath, $coverageBasePath, $relativeCoverageFilePath, $projectSource));
        }
    }
    private static function assertHasExecutedLines(SafeDOMXPath $xPath, bool $isForGitDiffLines) : void
    {
        $lineCoverage = $xPath->query('/phpunit/project/directory[1]/totals/lines')->item(0);
        if (!$lineCoverage instanceof DOMElement || ($coverageCount = $lineCoverage->getAttribute('executed')) === '0' || $coverageCount === '') {
            throw $isForGitDiffLines ? NoLineExecutedInDiffLinesMode::create() : NoLineExecuted::create();
        }
    }
    private static function getProjectSource(SafeDOMXPath $xPath) : string
    {
        $sourceNodes = $xPath->query('//project/@source');
        if ($sourceNodes->length > 0) {
            return $sourceNodes[0]->nodeValue;
        }
        return $xPath->query('//project/@name')[0]->nodeValue;
    }
}
