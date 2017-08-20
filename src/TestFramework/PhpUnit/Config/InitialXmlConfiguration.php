<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialXmlConfiguration extends AbstractXmlConfiguration
{
    /**
     * @var string
     */
    private $jUnitFilePath;

    /**
     * @var array
     */
    private $srcDirs = [];

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer, string $jUnitFilePath, array $srcDirs)
    {
        parent::__construct($tempDirectory, $originalXmlConfigPath, $pathReplacer);

        $this->jUnitFilePath = $jUnitFilePath;
        $this->srcDirs = $srcDirs;
    }

    public function getXml(): string
    {
        $originalXml = file_get_contents($this->originalXmlConfigPath);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($originalXml);

        $xPath = new \DOMXPath($dom);

        $this->addCoverageFilterWhitelistIfDoesNotExist($dom, $xPath);
        $this->replaceWithAbsolutePaths($xPath);
        $this->setStopOnFailure($xPath);
        $this->deactivateColours($xPath);
        $this->removeExistingLoggers($dom, $xPath);
        $this->addCodeCoverageLogger($dom, $xPath);
        $this->addJUnitLogger($dom, $xPath);

        return $dom->saveXML();
    }

    private function addJUnitLogger(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $junitLog = $dom->createElement('log');
        $junitLog->setAttribute('type', 'junit');
        $junitLog->setAttribute('target', $this->jUnitFilePath);

        $logging->appendChild($junitLog);
    }

    private function addCodeCoverageLogger(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $coverageXmlLog = $dom->createElement('log');
        $coverageXmlLog->setAttribute('type', 'coverage-xml');
        $coverageXmlLog->setAttribute('target', $this->tempDirectory . '/' . CodeCoverageData::PHP_UNIT_COVERAGE_DIR);

        $logging->appendChild($coverageXmlLog);
    }

    private function addCoverageFilterWhitelistIfDoesNotExist(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $filterNode = $this->getNode($dom, $xPath, 'filter');

        if ($filterNode === null) {
            $filterNode = $this->createNode($dom, 'filter');

            $whiteListNode = $dom->createElement('whitelist');

            foreach ($this->srcDirs as $srcDir) {
                $directoryNode = $dom->createElement(
                    'directory',
                        $srcDir
                );

                $whiteListNode->appendChild($directoryNode);
            }

            $filterNode->appendChild($whiteListNode);
        }
    }

    private function getOrCreateNode(\DOMDocument $dom, \DOMXPath $xPath, string $nodeName): \DOMElement
    {
        $node = $this->getNode($dom, $xPath, $nodeName);

        if ($node === null) {
            $node = $this->createNode($dom, $nodeName);
        }

        return $node;
    }

    private function getNode(\DOMDocument $dom, \DOMXPath $xPath, string $nodeName)
    {
        $nodeList = $xPath->query(sprintf('/phpunit/%s', $nodeName));

        if ($nodeList->length) {
            return $nodeList->item(0);
        }

        return null;
    }

    private function createNode(\DOMDocument $dom, string $nodeName): \DOMElement
    {
        $node = $dom->createElement($nodeName);
        $dom->documentElement->appendChild($node);

        return $node;
    }
}
