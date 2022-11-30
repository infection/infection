<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Safe\preg_replace;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class JUnitTestFileDataProvider implements TestFileDataProvider
{
    private ?SafeDOMXPath $xPath = null;
    public function __construct(private JUnitReportLocator $jUnitLocator)
    {
    }
    public function getTestFileInfo(string $fullyQualifiedClassName) : TestFileTimeData
    {
        $xPath = $this->getXPath();
        $nodes = null;
        foreach (self::testCaseMapGenerator($fullyQualifiedClassName) as $queryString => $placeholder) {
            $nodes = $xPath->query(sprintf($queryString, $placeholder));
            if ($nodes->length !== 0) {
                break;
            }
        }
        Assert::notNull($nodes);
        if ($nodes->length === 0) {
            throw TestFileNameNotFoundException::notFoundFromFQN($fullyQualifiedClassName, $this->jUnitLocator->locate());
        }
        Assert::same($nodes->length, 1);
        return new TestFileTimeData($nodes[0]->getAttribute('file'), (float) $nodes[0]->getAttribute('time'));
    }
    private static function testCaseMapGenerator(string $fullyQualifiedClassName) : iterable
    {
        (yield '//testsuite[@name="%s"][1]' => $fullyQualifiedClassName);
        (yield '//testcase[@class="%s"][1]' => $fullyQualifiedClassName);
        (yield '//testcase[contains(@file, "%s")][1]' => preg_replace('/^(.*):+.*$/', '$1.feature', $fullyQualifiedClassName));
        (yield '//testcase[@class="%s"][1]' => preg_replace('/^(.*):+.*$/', '$1', $fullyQualifiedClassName));
    }
    private function getXPath() : SafeDOMXPath
    {
        return $this->xPath ?? ($this->xPath = self::createXPath($this->jUnitLocator->locate()));
    }
    private static function createXPath(string $jUnitPath) : SafeDOMXPath
    {
        Assert::fileExists($jUnitPath);
        $dom = new DOMDocument();
        $success = @$dom->load($jUnitPath);
        Assert::true($success);
        return new SafeDOMXPath($dom);
    }
}
