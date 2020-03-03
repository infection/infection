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

use function array_filter;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use function file_exists;
use function implode;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\SafeDOMXPath;
use function Safe\file_get_contents;
use function Safe\preg_replace;
use function Safe\sprintf;
use function Safe\substr;
use function str_replace;
use function trim;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

/**
 * @internal
 */
class IndexXmlCoverageParser
{
    private $coverageDir;

    public function __construct(string $coverageDir)
    {
        $this->coverageDir = $coverageDir;
    }

    /**
     * Parses the given PHPUnit XML coverage index report (index.xml) to collect the general
     * coverage data. Note that this data is likely incomplete an will need to be enriched to
     * contain all the desired data.
     *
     * @throws NoLineExecuted
     *
     * @return CoverageFileData[]
     */
    public function parse(string $coverageXmlContent): array
    {
        $xPath = self::createXPath($coverageXmlContent);

        self::assertHasCoverage($xPath);

        $nodes = $xPath->query('//file');

        $projectSource = self::getProjectSource($xPath);

        $data = [];

        foreach ($nodes as $node) {
            $relativeFilePath = $node->getAttribute('href');

            $this->processCoverageFile($relativeFilePath, $projectSource, $data);
        }

        return $data;
    }

    private static function createXPath(string $coverageContent): SafeDOMXPath
    {
        $document = new DOMDocument();
        $success = @$document->loadXML(self::removeNamespace($coverageContent));

        Assert::true($success);

        return new SafeDOMXPath($document);
    }

    /**
     * Remove namespace to work with xPath without a headache
     */
    private static function removeNamespace(string $xml): string
    {
        /** @var string $cleanedXml */
        $cleanedXml = preg_replace('/xmlns=\".*?\"/', '', $xml);

        Assert::string($cleanedXml);

        return $cleanedXml;
    }

    /**
     * @throws NoLineExecuted
     */
    private static function assertHasCoverage(SafeDOMXPath $xPath): void
    {
        $lineCoverage = $xPath->query('/phpunit/project/directory[1]/totals/lines')->item(0);

        if (
            !($lineCoverage instanceof DOMElement)
            || ($coverageCount = $lineCoverage->getAttribute('executed')) === '0'
            || $coverageCount === ''
        ) {
            throw NoLineExecuted::create();
        }
    }

    /**
     * @param array<string, CoverageFileData> $data
     */
    private function processCoverageFile(
        string $relativeCoverageFilePath,
        string $projectSource,
        array &$data
    ): void {
        $coverageFilePath = Path::canonicalize(
            $this->coverageDir . '/' . $relativeCoverageFilePath
        );

        $xPath = self::createXPath(file_get_contents($coverageFilePath));

        $sourceFilePath = self::retrieveSourceFilePath(
            $coverageFilePath,
            $xPath,
            $relativeCoverageFilePath,
            $projectSource
        );

        $percentage = $xPath->query('/phpunit/file/totals/lines')[0]->getAttribute('percent');

        if (substr($percentage, -1) === '%') {
            // In PHPUnit <6 the percentage value would take the form "0.00%" in _some_ cases.
            // For example could find both with percentage and without in
            // https://github.com/maks-rafalko/tactician-domain-events/tree/1eb23434d3a833dedb6180ead75ff983ef09a2e9
            $percentage = substr($percentage, 0, -1);
        }

        if ($percentage === '') {
            $percentage = .0;
        } else {
            Assert::numeric($percentage);

            $percentage = (float) $percentage;
        }

        if ($percentage === .0) {
            $data[$sourceFilePath] = new CoverageFileData();

            return;
        }

        $coveredLineNodes = $xPath->query('/phpunit/file/coverage/line');

        if ($coveredLineNodes->length === 0) {
            $data[$sourceFilePath] = new CoverageFileData();

            return;
        }

        $coveredMethodNodes = $xPath->query('/phpunit/file/class/method');

        if ($coveredMethodNodes->length === 0) {
            $coveredMethodNodes = $xPath->query('/phpunit/file/trait/method');
        }

        $data[$sourceFilePath] = new CoverageFileData(
            self::collectCoveredLinesData($coveredLineNodes),
            self::collectMethodsCoverageData($coveredMethodNodes)
        );
    }

    private static function retrieveSourceFilePath(
        string $coverageFilePath,
        SafeDOMXPath $xPath,
        string $relativeCoverageFilePath,
        string $projectSource
    ): string {
        $fileNode = $xPath->query('/phpunit/file')[0];

        Assert::notNull($fileNode);

        $fileName = $fileNode->getAttribute('name');
        $relativeFilePath = $fileNode->getAttribute('path');

        if ($relativeFilePath === '') {
            // The relative path is not present for old versions of PHPUnit. As a result we parse
            // the relative path from the source file path and the XML coverage file
            $relativeFilePath = str_replace(
                sprintf('%s.xml', $fileName),
                '',
                $relativeCoverageFilePath
            );
        }

        $path = Path::canonicalize(implode(
            '/',
            array_filter([$projectSource, trim($relativeFilePath, '/'), $fileName])
        ));

        if (!file_exists($path)) {
            throw new InvalidCoverage(sprintf(
                'Could not find the source file "%s" referred by "%s". Make sure the '
                . 'coverage used is up to date',
                $path,
                $coverageFilePath
            ));
        }

        return $path;
    }

    /**
     * @param DOMNodeList|DOMElement[] $coveredLineNodes
     * @phpstan-param DOMNodeList<DOMElement> $coveredLineNodes
     *
     * @return array<int, array<int, CoverageLineData>>
     */
    private static function collectCoveredLinesData(DOMNodeList $coveredLineNodes): array
    {
        $data = [];

        foreach ($coveredLineNodes as $lineNode) {
            $lineNumber = $lineNode->getAttribute('nr');

            Assert::integerish($lineNumber);

            $lineNumber = (int) $lineNumber;

            /** @phpstan-var DOMNodeList<DOMElement> $coveredNodes */
            $coveredNodes = $lineNode->childNodes;

            foreach ($coveredNodes as $coveredNode) {
                if ($coveredNode->nodeName !== 'covered') {
                    continue;
                }

                $data[$lineNumber][] = CoverageLineData::withTestMethod(
                    $coveredNode->getAttribute('by')
                );
            }
        }

        return $data;
    }

    /**
     * @param DOMNodeList|DOMElement[] $methodsCoverageNodes
     * @phpstan-param DOMNodeList<DOMElement> $methodsCoverageNodes
     *
     * @return MethodLocationData[]
     */
    private static function collectMethodsCoverageData(DOMNodeList $methodsCoverageNodes): array
    {
        $methodsCoverage = [];

        foreach ($methodsCoverageNodes as $methodsCoverageNode) {
            $methodName = $methodsCoverageNode->getAttribute('name');

            $start = $methodsCoverageNode->getAttribute('start');
            $end = $methodsCoverageNode->getAttribute('end');

            Assert::integerish($start);
            Assert::integerish($end);

            $methodsCoverage[$methodName] = new MethodLocationData(
                (int) $start,
                (int) $end
            );
        }

        return $methodsCoverage;
    }

    private static function getProjectSource(SafeDOMXPath $xPath): string
    {
        // PHPUnit >= 6
        $sourceNodes = $xPath->query('//project/@source');

        if ($sourceNodes->length > 0) {
            return $sourceNodes[0]->nodeValue;
        }

        // PHPUnit < 6
        return $xPath->query('//project/@name')[0]->nodeValue;
    }
}
