<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Coverage;

class CoverageXmlParser
{
    /**
     * @var string
     */
    private $srcDir;

    /**
     * @var string
     */
    private $coverageDir;

    public function __construct(string $coverageDir, string $srcDir)
    {
        $this->coverageDir = $coverageDir;
        $this->srcDir = $srcDir;
    }

    /**
     * @param string $coverageXmlContent
     * @return array
     */
    public function parse(string $coverageXmlContent): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageXmlContent));
        $xPath = new \DOMXPath($dom);

        $coverage = [];

        $nodes = $xPath->query('//file');

        foreach ($nodes as $node) {
            $relativeFilePath = $node->getAttribute('href');

            $fileCoverage = $this->processXmlFileCoverage($relativeFilePath, $node);

            $coverage = array_merge($coverage, $fileCoverage);
        }

        return $coverage;
    }

    /**
     * @param string $relativeCoverageFilePath
     * @param \DOMElement $indexFileNode
     * @return array
     */
    private function processXmlFileCoverage(string $relativeCoverageFilePath, \DOMElement $indexFileNode): array
    {
        $absolutePath = realpath($this->coverageDir . '/' . $relativeCoverageFilePath);
        $coverageFileXml = file_get_contents($absolutePath);

        $dom = new \DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageFileXml));
        $xPath = new \DOMXPath($dom);

        $sourceFilePath = $this->getSourceFilePath($xPath, $relativeCoverageFilePath);

        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];
        $percentage = $linesNode->getAttribute('percent');

        if ($percentage === '0.00' || empty($percentage)) {
            return [$sourceFilePath => []];
        }

        /** @var \DOMNodeList $lineCoverageNodes */
        $lineCoverageNodes = $xPath->query('/phpunit/file/coverage/line');

        if ($lineCoverageNodes->length === 0) {
            return [$sourceFilePath => []];
        }

        return [$sourceFilePath => $this->getCoveredLinesData($lineCoverageNodes)];
    }

    /**
     * Remove namespace to work with xPath without a headache
     *
     * @param string $xml
     * @return string
     */
    private function removeNamespace(string $xml): string
    {
        return preg_replace('/xmlns=\".*?\"/', '', $xml);
    }

    /**
     * @param \DOMXPath $xPath
     * @param string $relativeCoverageFilePath
     * @return string
     */
    private function getSourceFilePath(\DOMXPath $xPath, string $relativeCoverageFilePath): string
    {
        $fileNode = $xPath->query('/phpunit/file')[0];
        $fileName = $fileNode->getAttribute('name');
        $relativeFilePath = $fileNode->getAttribute('path');

        if (! $relativeFilePath) {
            // path is not present for old versions of PHPUnit, so parth the source file path from
            // the path of XML coverage file
            $relativeFilePath = str_replace(
                sprintf('%s.xml', $fileName),
                    '',
                    $relativeCoverageFilePath
            );
        }

        return realpath($this->srcDir . '/' . $relativeFilePath . '/' . $fileName);
    }

    /**
     * @param \DOMNodeList $lineCoverageNodes
     * @return array
     */
    private function getCoveredLinesData(\DOMNodeList $lineCoverageNodes): array
    {
        $fileCoverage = [];

        foreach ($lineCoverageNodes as $lineCoverageNode) {
            /** @var \DOMNode $lineCoverageNode */
            $lineNumber = (int) $lineCoverageNode->getAttribute('nr');

            foreach ($lineCoverageNode->childNodes as $coveredNode) {
                if ($coveredNode->nodeName === 'covered') {
                    $testMethod = $coveredNode->getAttribute('by');

                    $fileCoverage[$lineNumber][] = $testMethod;
                }
            }
        }

        return $fileCoverage;
    }
}