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
use DOMNameSpaceNode;
use DOMNode;
use DOMNodeList;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\SafeDOMXPath;
use Infection\TestFramework\Tracing\Trace\ProxyTrace;
use Infection\TestFramework\Tracing\Trace\SourceMethodLineRange;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use function Later\lazy;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class XmlCoverageParser
{
    public function __construct(
        private readonly FileSystem $fileSystem,
    ) {
    }

    public function parse(SourceFileInfoProvider $provider): Trace
    {
        $fileInfo = $provider->provideFileInfo();

        return new ProxyTrace(
            $fileInfo,
            $this->fileSystem->realPath($fileInfo->getPathname()),
            lazy(self::createTestLocationsGenerator($provider->provideXPath())),
        );
    }

    /**
     * @return iterable<TestLocations>
     */
    private static function createTestLocationsGenerator(SafeDOMXPath $xPath): iterable
    {
        yield self::retrieveTestLocations($xPath);
    }

    private static function retrieveTestLocations(SafeDOMXPath $xPath): TestLocations
    {
        $percentage = $xPath
            ->getElement('/p:phpunit/p:file/p:totals/p:lines')
            ->getAttribute('percent');

        if (self::percentageToFloat($percentage) === .0) {
            return new TestLocations();
        }

        $coveredLineNodes = $xPath->queryList('/p:phpunit/p:file/p:coverage/p:line');

        if ($coveredLineNodes->length === 0) {
            return new TestLocations();
        }

        $coveredMethodNodes = $xPath->queryList('/p:phpunit/p:file/p:class/p:method');

        if ($coveredMethodNodes->length === 0) {
            $coveredMethodNodes = $xPath->queryList('/p:phpunit/p:file/p:trait/p:method');
        }

        return new TestLocations(
            self::collectCoveredLinesData($coveredLineNodes),
            self::collectMethodsCoverageData($coveredMethodNodes),
        );
    }

    private static function percentageToFloat(string $percentage): float
    {
        // In PHPUnit <6 the percentage value would take the form "0.00%" in _some_ cases.
        // For example could find both with percentage and without in
        // https://github.com/maks-rafalko/tactician-domain-events/tree/1eb23434d3a833dedb6180ead75ff983ef09a2e9

        // But PHP can handle them all. Together with an empty string.
        return (float) $percentage;
    }

    /**
     * @param DOMNodeList<DOMNode|DOMNameSpaceNode> $coveredLineNodes
     *
     * @return array<int, array<int, TestLocation>>
     */
    private static function &collectCoveredLinesData(DOMNodeList $coveredLineNodes): array
    {
        $data = [];

        foreach ($coveredLineNodes as $lineNode) {
            Assert::isInstanceOf($lineNode, DOMElement::class);

            $lineNumber = $lineNode->getAttribute('nr');

            Assert::integerish($lineNumber);

            $lineNumber = (int) $lineNumber;

            $coveredNodes = $lineNode->childNodes;

            foreach ($coveredNodes as $coveredNode) {
                if ($coveredNode->nodeName !== 'covered') {
                    continue;
                }

                Assert::isInstanceOf($coveredNode, DOMElement::class);

                $data[$lineNumber][] = TestLocation::forTestMethod(
                    $coveredNode->getAttribute('by'),
                );
            }
        }

        return $data;
    }

    /**
     * @param DOMNodeList<DOMNode|DOMNameSpaceNode> $methodsCoverageNodes
     *
     * @return SourceMethodLineRange[]
     */
    private static function &collectMethodsCoverageData(DOMNodeList $methodsCoverageNodes): array
    {
        $methodsCoverage = [];

        foreach ($methodsCoverageNodes as $methodsCoverageNode) {
            Assert::isInstanceOf($methodsCoverageNode, DOMElement::class);

            if ((int) $methodsCoverageNode->getAttribute('coverage') === 0) {
                continue;
            }

            $methodName = $methodsCoverageNode->getAttribute('name');

            $start = $methodsCoverageNode->getAttribute('start');
            $end = $methodsCoverageNode->getAttribute('end');

            Assert::integerish($start);
            Assert::integerish($end);

            $methodsCoverage[$methodName] = new SourceMethodLineRange(
                (int) $start,
                (int) $end,
            );
        }

        return $methodsCoverage;
    }
}
