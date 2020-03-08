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
use DOMElement;
use DOMNodeList;
use function implode;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoveredFileData;
use Infection\TestFramework\Coverage\MethodLocationData;
use Infection\TestFramework\SafeDOMXPath;
use function realpath as native_realpath;
use function Safe\file_get_contents;
use function Safe\sprintf;
use function Safe\substr;
use function str_replace;
use Symfony\Component\Finder\SplFileInfo;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class XmlCoverageParser
{
    private $coverageDir;
    private $relativeCoverageFilePath;
    private $projectSource;

    public function __construct(
        string $coverageDir,
        string $relativeCoverageFilePath,
        string $projectSource
    ) {
        $this->coverageDir = $coverageDir;
        $this->relativeCoverageFilePath = $relativeCoverageFilePath;
        $this->projectSource = $projectSource;
    }

    /**
     * Parses the given PHPUnit XML coverage index report (index.xml) to collect the general
     * coverage data. Note that this data is likely incomplete an will need to be enriched to
     * contain all the desired data.
     *
     * @throws NoLineExecuted
     */
    public function parse(): CoveredFileData
    {
        $xPath = IndexXmlCoverageParser::createXPath(file_get_contents(
            $this->coverageDir . '/' . $this->relativeCoverageFilePath
        ));

        $sourceFileInfo = self::retrieveSourceFileInfo($xPath, $this->relativeCoverageFilePath, $this->projectSource);

        return new CoveredFileData($sourceFileInfo, $this->retrieveCoverageFileData($xPath));
    }

    /** @return iterable<CoverageFileData> */
    private function retrieveCoverageFileData(SafeDOMXPath $xPath): iterable
    {
        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];

        $percentage = $linesNode->getAttribute('percent');

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
            yield new CoverageFileData();

            return;
        }

        $coveredLineNodes = $xPath->query('/phpunit/file/coverage/line');

        if ($coveredLineNodes->length === 0) {
            yield new CoverageFileData();

            return;
        }

        $coveredMethodNodes = $xPath->query('/phpunit/file/class/method');

        if ($coveredMethodNodes->length === 0) {
            $coveredMethodNodes = $xPath->query('/phpunit/file/trait/method');
        }

        yield new CoverageFileData(
            self::collectCoveredLinesData($coveredLineNodes),
            self::collectMethodsCoverageData($coveredMethodNodes)
        );
    }

    private static function retrieveSourceFileInfo(
        SafeDOMXPath $xPath,
        string $relativeCoverageFilePath,
        string $projectSource
    ): SplFileInfo {
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

        $path = implode(
            '/',
            array_filter([$projectSource, trim($relativeFilePath, '/'), $fileName])
        );

        $realPath = native_realpath($path);

        if ($realPath === false) {
            throw new InvalidCoverage(sprintf(
                'Could not find the source file "%s" referred by "%s". Make sure the '
                . 'coverage used is up to date',
                $path,
                $relativeFilePath
            ));
        }

        return new SplFileInfo($realPath, $relativeFilePath, $path);
    }

    /**
     * @param DOMNodeList|DOMElement[] $coveredLineNodes
     * @phpstan-param DOMNodeList<DOMElement> $coveredLineNodes
     *
     * @return array<int, array<int, CoverageLineData>>
     */
    private static function &collectCoveredLinesData(DOMNodeList $coveredLineNodes): array
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
    private static function &collectMethodsCoverageData(DOMNodeList $methodsCoverageNodes): array
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
}
