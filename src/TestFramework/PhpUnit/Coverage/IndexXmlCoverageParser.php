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
use Infection\TestFramework\Coverage\CoverageFileData;
use Infection\TestFramework\Coverage\CoveredFileData;
use Infection\TestFramework\SafeDOMXPath;
use function Pipeline\take;
use function Safe\preg_replace;
use Webmozart\Assert\Assert;

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
     * @return iterable<CoveredFileData>
     */
    public function parseLazy(string $coverageXmlContent): iterable
    {
        $xPath = self::createXPath($coverageXmlContent);

        self::assertHasCoverage($xPath);

        return $this->parseNodes($xPath);
    }

    /**
     * @deprecated in favor of parseLazy
     *
     * @return array<string, CoverageFileData>
     */
    public function parse(string $coverageXmlContent): array
    {
        $coverage = take($this->parseLazy($coverageXmlContent))
            ->map(static function (CoveredFileData $data) {
                yield $data->getSplFileInfo()->getRealPath() => $data->retrieveCoverageFileData();
            });

        return iterator_to_array($coverage, true);
    }

    public static function createXPath(string $coverageContent): SafeDOMXPath
    {
        $document = new DOMDocument();
        $success = @$document->loadXML(self::removeNamespace($coverageContent));

        Assert::true($success);

        return new SafeDOMXPath($document);
    }

    /**
     * @return iterable<CoveredFileData>
     */
    private function parseNodes(SafeDOMXPath $xPath): iterable
    {
        $projectSource = self::getProjectSource($xPath);

        $nodes = $xPath->query('//file');

        foreach ($nodes as $node) {
            $relativeCoverageFilePath = $node->getAttribute('href');

            $parser = new XmlCoverageParser($this->coverageDir, $relativeCoverageFilePath, $projectSource);

            yield $parser->parse();
        }
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
