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

namespace Infection\TestFramework\Coverage\XmlReport;

use DOMElement;
use Infection\FileSystem\FileSystem;
use Infection\Source\Exception\NoSourceFound;
use Infection\TestFramework\SafeDOMXPath;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class IndexXmlCoverageParser
{
    public function __construct(
        private readonly bool $isSourceFiltered,
        private readonly FileSystem $fileSystem,
    ) {
    }

    /**
     * Parses the given PHPUnit XML coverage index report (index.xml) to collect the information
     * needed to parse general coverage data. Note that this data is likely incomplete an will
     * need to be enriched to contain all the desired data.
     *
     * @throws InvalidCoverage
     * @throws NoSourceFound
     *
     * @return iterable<SourceFileInfoProvider>
     */
    public function parse(
        string $coverageIndexPath,
        string $coverageBasePath,
    ): iterable {
        $xPath = SafeDOMXPath::fromFile($coverageIndexPath, 'p');

        self::assertHasExecutedLines($xPath, $this->isSourceFiltered);

        return $this->parseNodes($coverageIndexPath, $coverageBasePath, $xPath);
    }

    /**
     * @throws InvalidCoverage
     *
     * @return iterable<SourceFileInfoProvider>
     */
    private function parseNodes(
        string $coverageIndexPath,
        string $coverageBasePath,
        SafeDOMXPath $xPath,
    ): iterable {
        $projectSource = self::getProjectSource($coverageIndexPath, $xPath);

        foreach ($xPath->queryList('//p:file') as $node) {
            Assert::isInstanceOf($node, DOMElement::class);

            $relativeCoverageFilePath = $node->getAttribute('href');

            yield new SourceFileInfoProvider(
                $coverageIndexPath,
                $coverageBasePath,
                $relativeCoverageFilePath,
                $projectSource,
                $this->fileSystem,
            );
        }
    }

    /**
     * @throws NoSourceFound
     */
    private static function assertHasExecutedLines(SafeDOMXPath $xPath, bool $isSourceFiltered): void
    {
        $lineCoverage = $xPath->queryElement('/p:phpunit/p:project/p:directory[1]/p:totals/p:lines');

        if (
            $lineCoverage === null
            || ($coverageCount = $lineCoverage->getAttribute('executed')) === '0'
            || $coverageCount === ''
        ) {
            throw $isSourceFiltered
                ? NoSourceFound::noExecutableSourceCodeForDiff()
                : NoSourceFound::noExecutableSourceCode();
        }
    }

    /**
     * @throws InvalidCoverage
     */
    private static function getProjectSource(string $pathname, SafeDOMXPath $xPath): string
    {
        $sourceQueries = [
            '//p:project/@source',  // PHPUnit >= 6
            '//p:project/@name',    // PHPUnit < 6
        ];

        foreach ($sourceQueries as $sourceQuery) {
            $source = $xPath->queryAttribute($sourceQuery)?->nodeValue;

            if ($source !== null) {
                return $source;
            }
        }

        throw new InvalidCoverage(
            sprintf(
                'Could not find the source attribute for the project in the file "%s".',
                $pathname,
            ),
        );
    }
}
