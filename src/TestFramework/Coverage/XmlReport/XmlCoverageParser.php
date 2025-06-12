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
use DOMNodeList;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\ProxyTrace;
use Infection\TestFramework\Coverage\SourceMethodLineRange;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\SafeDOMXPath;
use function Later\lazy;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class XmlCoverageParser
{
    public function __construct()
    {
    }

    public function parse(SourceFileInfoProvider $provider): Trace
    {
        return new ProxyTrace(
            $provider->provideFileInfo(),
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
        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];

        $percentage = $linesNode->getAttribute('percent');

        if (self::percentageToFloat($percentage) === .0) {
            return new TestLocations();
        }

        $coveredLineNodes = $xPath->query('/phpunit/file/coverage/line');

        if ($coveredLineNodes->length === 0) {
            return new TestLocations();
        }

        $coveredMethodNodes = $xPath->query('/phpunit/file/class/method');

        if ($coveredMethodNodes->length === 0) {
            $coveredMethodNodes = $xPath->query('/phpunit/file/trait/method');
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
     * @param DOMNodeList<DOMElement> $coveredLineNodes
     *
     * @return array<int, array<int, TestLocation>>
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

                $data[$lineNumber][] = TestLocation::forTestMethod(
                    $coveredNode->getAttribute('by'),
                );
            }
        }

        return $data;
    }

    /**
     * @param DOMNodeList<DOMElement> $methodsCoverageNodes
     *
     * @return SourceMethodLineRange[]
     */
    private static function &collectMethodsCoverageData(DOMNodeList $methodsCoverageNodes): array
    {
        $methodsCoverage = [];

        foreach ($methodsCoverageNodes as $methodsCoverageNode) {
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
