<?php

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

abstract class AbstractXmlConfiguration
{
    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @var string
     */
    protected $originalXmlConfigPath;

    /**
     * @var PathReplacer
     */
    protected $pathReplacer;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->pathReplacer = $pathReplacer;
    }

    abstract public function getXml() : string;

    protected function replaceWithAbsolutePaths(\DOMXPath $xPath)
    {
        $queries = [
            '/phpunit/@bootstrap',
            '/phpunit/testsuites/testsuite/exclude',
            '//directory',
            '//file',
        ];

        $nodes = $xPath->query(implode('|', $queries));

        foreach ($nodes as $node) {
            $this->pathReplacer->replaceInNode($node);
        }
    }

    protected function removeExistingLoggers(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $nodes = $xPath->query('/phpunit/logging');

        foreach ($nodes as $node) {
            $dom->documentElement->removeChild($node);
        }
    }

    protected function addCodeCoverageLogger(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $loggingList = $xPath->query('/phpunit/logging');

        // TODO reuse
        if ($loggingList->length) {
            $logging = $loggingList->item(0);
        } else {
            $logging = $dom->createElement('logging');
            $dom->documentElement->appendChild($logging);
        }

        $coverageXmlLog = $dom->createElement('log');
        $coverageXmlLog->setAttribute('type', 'coverage-xml');
        $coverageXmlLog->setAttribute('target', $this->tempDirectory . '/' . CodeCoverageData::PHP_UNIT_COVERAGE_DIR);

        $logging->appendChild($coverageXmlLog);
    }

    protected function setStopOnFailure(\DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@stopOnFailure');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'true';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('stopOnFailure', 'true');
        }
    }

    protected function deactivateColours(\DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@colors');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'false';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('colors', 'false');
        }
    }
}