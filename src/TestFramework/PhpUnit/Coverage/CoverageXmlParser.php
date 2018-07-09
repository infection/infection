<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Coverage;

use Infection\TestFramework\Coverage\CoverageDoesNotExistException;

/**
 * @internal
 */
class CoverageXmlParser
{
    /**
     * @var string
     */
    private $coverageDir;

    public function __construct(string $coverageDir)
    {
        $this->coverageDir = $coverageDir;
    }

    /**
     * @param string $coverageXmlContent
     *
     * @return array
     */
    public function parse(string $coverageXmlContent): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageXmlContent));
        $xPath = new \DOMXPath($dom);

        $coverage = [[]];

        $nodes = $xPath->query('//file');
        $projectSource = $this->getProjectSource($xPath);

        foreach ($nodes as $node) {
            $relativeFilePath = $node->getAttribute('href');

            $fileCoverage = $this->processXmlFileCoverage($relativeFilePath, $projectSource);

            $coverage[] = $fileCoverage;
        }

        return array_merge(...$coverage);
    }

    /**
     * @param string $relativeCoverageFilePath
     * @param string $projectSource
     *
     * @return array
     */
    private function processXmlFileCoverage(string $relativeCoverageFilePath, string $projectSource): array
    {
        $absolutePath = realpath($this->coverageDir . '/' . $relativeCoverageFilePath);
        \assert(\is_string($absolutePath));

        $coverageFileXml = file_get_contents($absolutePath);
        \assert(\is_string($coverageFileXml));

        $dom = new \DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageFileXml));
        $xPath = new \DOMXPath($dom);

        $sourceFilePath = $this->getSourceFilePath($xPath, $relativeCoverageFilePath, $projectSource);

        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];
        $percentage = (float) $linesNode->getAttribute('percent');

        $defaultCoverageFileData = ['byLine' => [], 'byMethod' => []];

        if (!$percentage) {
            return [$sourceFilePath => $defaultCoverageFileData];
        }

        /** @var \DOMNodeList $lineCoverageNodes */
        $lineCoverageNodes = $xPath->query('/phpunit/file/coverage/line');

        if (!$lineCoverageNodes->length) {
            return [$sourceFilePath => $defaultCoverageFileData];
        }

        $methodsCoverageNodes = $xPath->query('/phpunit/file/class/method');

        if (!$methodsCoverageNodes->length) {
            $methodsCoverageNodes = $xPath->query('/phpunit/file/trait/method');
        }

        return [
            $sourceFilePath => [
                'byLine' => $this->getCoveredLinesData($lineCoverageNodes),
                'byMethod' => $this->getMethodsCoverageData($methodsCoverageNodes),
            ],
        ];
    }

    /**
     * Remove namespace to work with xPath without a headache
     *
     * @param string $xml
     *
     * @return string
     */
    private function removeNamespace(string $xml): string
    {
        return preg_replace('/xmlns=\".*?\"/', '', $xml);
    }

    /**
     * @param \DOMXPath $xPath
     * @param string $relativeCoverageFilePath
     * @param string $projectSource
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getSourceFilePath(\DOMXPath $xPath, string $relativeCoverageFilePath, string $projectSource): string
    {
        $fileNode = $xPath->query('/phpunit/file')[0];
        $fileName = $fileNode->getAttribute('name');
        $relativeFilePath = $fileNode->getAttribute('path');

        if (!$relativeFilePath) {
            // path is not present for old versions of PHPUnit, so parse the source file path from
            // the path of XML coverage file
            $relativeFilePath = str_replace(
                sprintf('%s.xml', $fileName),
                    '',
                    $relativeCoverageFilePath
            );
        }

        $path = $projectSource . '/' . ltrim($relativeFilePath, '/') . '/' . $fileName;
        $realPath = realpath($path);

        if ($realPath) {
            return $realPath;
        }

        throw CoverageDoesNotExistException::forFileAtPath($fileName, $path);
    }

    private function getCoveredLinesData(\DOMNodeList $lineCoverageNodes): array
    {
        $fileCoverage = [];

        foreach ($lineCoverageNodes as $lineCoverageNode) {
            /** @var \DOMNode $lineCoverageNode */
            $lineNumber = $lineCoverageNode->getAttribute('nr');

            foreach ($lineCoverageNode->childNodes as $coveredNode) {
                if ($coveredNode->nodeName === 'covered') {
                    $testMethod = $coveredNode->getAttribute('by');

                    $fileCoverage[$lineNumber][] = ['testMethod' => $testMethod];
                }
            }
        }

        return $fileCoverage;
    }

    private function getMethodsCoverageData(\DOMNodeList $methodsCoverageNodes): array
    {
        $methodsCoverage = [];

        foreach ($methodsCoverageNodes as $methodsCoverageNode) {
            $methodName = $methodsCoverageNode->getAttribute('name');

            $methodsCoverage[$methodName] = [
                'startLine' => (int) $methodsCoverageNode->getAttribute('start'),
                'endLine' => (int) $methodsCoverageNode->getAttribute('end'),
                'executable' => (int) $methodsCoverageNode->getAttribute('executable'),
                'executed' => (int) $methodsCoverageNode->getAttribute('executed'),
                'coverage' => (int) $methodsCoverageNode->getAttribute('coverage'),
            ];
        }

        return $methodsCoverage;
    }

    private function getProjectSource(\DOMXPath $xPath): string
    {
        // phpunit >= 6
        $sourceNodes = $xPath->query('//project/@source');

        if ($sourceNodes->length > 0) {
            return $sourceNodes[0]->nodeValue;
        }

        // phpunit < 6
        return $xPath->query('//project/@name')[0]->nodeValue;
    }
}
