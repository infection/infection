<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Coverage;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use Infection\TestFramework\Coverage\CoverageDoesNotExistException;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\PhpUnit\Coverage\Exception\NoLinesExecutedException;
use function Safe\file_get_contents;
use function Safe\realpath;

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
     * @return CoverageFileData[]
     *
     * @throws Exception
     */
    public function parse(string $coverageXmlContent): array
    {
        $dom = new DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageXmlContent));
        $xPath = new DOMXPath($dom);

        $this->assertHasCoverage($xPath);

        $coverage = [[]];

        $nodes = $xPath->query('//file');
        $projectSource = $this->getProjectSource($xPath);

        foreach ($nodes as $node) {
            $relativeFilePath = $node->getAttribute('href');

            $coverage[] = $this->processXmlFileCoverage($relativeFilePath, $projectSource);
        }

        return array_merge(...$coverage);
    }

    private function assertHasCoverage(DOMXPath $xPath): void
    {
        $lineCoverage = $xPath->query('/phpunit/project/directory[1]/totals/lines')->item(0);

        if (
            !$lineCoverage instanceof DOMElement
            || ($coverageCount = $lineCoverage->getAttribute('executed')) === '0'
            || $coverageCount === ''
        ) {
            throw NoLinesExecutedException::noLinesExecuted();
        }
    }

    /**
     * @return array<string, CoverageFileData>
     *
     * @throws Exception
     */
    private function processXmlFileCoverage(string $relativeCoverageFilePath, string $projectSource): array
    {
        $absolutePath = realpath($this->coverageDir . '/' . $relativeCoverageFilePath);
        $coverageFileXml = file_get_contents($absolutePath);

        $dom = new DOMDocument();
        $dom->loadXML($this->removeNamespace($coverageFileXml));
        $xPath = new DOMXPath($dom);

        $sourceFilePath = $this->getSourceFilePath($xPath, $relativeCoverageFilePath, $projectSource);

        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];
        $percentage = (float) $linesNode->getAttribute('percent');

        if (!$percentage) {
            return [$sourceFilePath => new CoverageFileData()];
        }

        /** @var DOMNodeList $lineCoverageNodes */
        $lineCoverageNodes = $xPath->query('/phpunit/file/coverage/line');

        if (!$lineCoverageNodes->length) {
            return [$sourceFilePath => new CoverageFileData()];
        }

        $methodsCoverageNodes = $xPath->query('/phpunit/file/class/method');

        if (!$methodsCoverageNodes->length) {
            $methodsCoverageNodes = $xPath->query('/phpunit/file/trait/method');
        }

        return [
            $sourceFilePath => new CoverageFileData(
                $this->getCoveredLinesData($lineCoverageNodes),
                $this->getMethodsCoverageData($methodsCoverageNodes)
            ),
        ];
    }

    /**
     * Remove namespace to work with xPath without a headache
     */
    private function removeNamespace(string $xml): string
    {
        return (string) preg_replace('/xmlns=\".*?\"/', '', $xml);
    }

    /**
     * @throws Exception
     */
    private function getSourceFilePath(DOMXPath $xPath, string $relativeCoverageFilePath, string $projectSource): string
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

    /**
     * @return array<int, array<int, CoverageLineData>>
     */
    private function getCoveredLinesData(DOMNodeList $lineCoverageNodes): array
    {
        $fileCoverage = [];

        foreach ($lineCoverageNodes as $lineCoverageNode) {
            /** @var DOMNode $lineCoverageNode */
            $lineNumber = (int) $lineCoverageNode->getAttribute('nr');

            foreach ($lineCoverageNode->childNodes as $coveredNode) {
                if ($coveredNode->nodeName === 'covered') {
                    $testMethod = $coveredNode->getAttribute('by');

                    $fileCoverage[$lineNumber][] = CoverageLineData::withTestMethod($testMethod);
                }
            }
        }

        return $fileCoverage;
    }

    /**
     * @return MethodLocationData[]
     */
    private function getMethodsCoverageData(DOMNodeList $methodsCoverageNodes): array
    {
        $methodsCoverage = [];

        foreach ($methodsCoverageNodes as $methodsCoverageNode) {
            $methodName = $methodsCoverageNode->getAttribute('name');

            $methodsCoverage[$methodName] = new MethodLocationData(
                (int) $methodsCoverageNode->getAttribute('start'),
                (int) $methodsCoverageNode->getAttribute('end')
            );
        }

        return $methodsCoverage;
    }

    private function getProjectSource(DOMXPath $xPath): string
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
