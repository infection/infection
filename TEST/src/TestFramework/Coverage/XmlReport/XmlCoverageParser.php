<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use DOMElement;
use DOMNodeList;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\ProxyTrace;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\SourceMethodLineRange;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\TestLocations;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\Trace;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Later\lazy;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class XmlCoverageParser
{
    public function __construct()
    {
    }
    public function parse(SourceFileInfoProvider $provider) : Trace
    {
        return new ProxyTrace($provider->provideFileInfo(), lazy(self::createTestLocationsGenerator($provider->provideXPath())));
    }
    private static function createTestLocationsGenerator(SafeDOMXPath $xPath) : iterable
    {
        (yield self::retrieveTestLocations($xPath));
    }
    private static function retrieveTestLocations(SafeDOMXPath $xPath) : TestLocations
    {
        $linesNode = $xPath->query('/phpunit/file/totals/lines')[0];
        $percentage = $linesNode->getAttribute('percent');
        if (self::percentageToFloat($percentage) === 0.0) {
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
        return new TestLocations(self::collectCoveredLinesData($coveredLineNodes), self::collectMethodsCoverageData($coveredMethodNodes));
    }
    private static function percentageToFloat(string $percentage) : float
    {
        return (float) $percentage;
    }
    private static function &collectCoveredLinesData(DOMNodeList $coveredLineNodes) : array
    {
        $data = [];
        foreach ($coveredLineNodes as $lineNode) {
            $lineNumber = $lineNode->getAttribute('nr');
            Assert::integerish($lineNumber);
            $lineNumber = (int) $lineNumber;
            /**
            @phpstan-var */
            $coveredNodes = $lineNode->childNodes;
            foreach ($coveredNodes as $coveredNode) {
                if ($coveredNode->nodeName !== 'covered') {
                    continue;
                }
                $data[$lineNumber][] = TestLocation::forTestMethod($coveredNode->getAttribute('by'));
            }
        }
        return $data;
    }
    private static function &collectMethodsCoverageData(DOMNodeList $methodsCoverageNodes) : array
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
            $methodsCoverage[$methodName] = new SourceMethodLineRange((int) $start, (int) $end);
        }
        return $methodsCoverage;
    }
}
