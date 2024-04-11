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
use Infection\TestFramework\SafeDOMXPath;

/**
 * @internal
 * @final
 */
class IndexXmlCoverageParser
{
    public function __construct(private readonly bool $isForGitDiffLines)
    {
    }

    /**
     * Parses the given PHPUnit XML coverage index report (index.xml) to collect the information
     * needed to parse general coverage data. Note that this data is likely incomplete an will
     * need to be enriched to contain all the desired data.
     *
     * @throws NoLineExecuted
     *
     * @return iterable<SourceFileInfoProvider>
     */
    public function parse(
        string $coverageIndexPath,
        string $xmlIndexCoverageContent,
        string $coverageBasePath,
    ): iterable {
        $xPath = XPathFactory::createXPath($xmlIndexCoverageContent);

        self::assertHasExecutedLines($xPath, $this->isForGitDiffLines);

        return $this->parseNodes($coverageIndexPath, $coverageBasePath, $xPath);
    }

    /**
     * @return iterable<SourceFileInfoProvider>
     */
    private function parseNodes(
        string $coverageIndexPath,
        string $coverageBasePath,
        SafeDOMXPath $xPath,
    ): iterable {
        $projectSource = self::getProjectSource($xPath);

        foreach ($xPath->query('//file') as $node) {
            $relativeCoverageFilePath = $node->getAttribute('href');

            yield new SourceFileInfoProvider(
                $coverageIndexPath,
                $coverageBasePath,
                $relativeCoverageFilePath,
                $projectSource,
            );
        }
    }

    /**
     * @throws NoLineExecuted
     */
    private static function assertHasExecutedLines(SafeDOMXPath $xPath, bool $isForGitDiffLines): void
    {
        $lineCoverage = $xPath->query('/phpunit/project/directory[1]/totals/lines')->item(0);

        if (
            !$lineCoverage instanceof DOMElement
            || ($coverageCount = $lineCoverage->getAttribute('executed')) === '0'
            || $coverageCount === ''
        ) {
            throw $isForGitDiffLines
                ? NoLineExecutedInDiffLinesMode::create()
                : NoLineExecuted::create();
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
