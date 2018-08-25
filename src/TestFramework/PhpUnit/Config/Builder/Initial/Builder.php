<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder\Initial;

use Infection\TestFramework\Config\Builder\Initial\AbstractBuilder;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;

/**
 * @internal
 */
class Builder extends AbstractBuilder
{
    /**
     * @var XmlConfigurationHelper
     */
    private $xmlConfigurationHelper;

    /**
     * @var string
     */
    private $jUnitFilePath;

    /**
     * @var array
     */
    private $srcDirs = [];

//    public function __construct(
//        string $originalXmlConfigContent,
//        XmlConfigurationHelper $xmlConfigurationHelper,
//        string $jUnitFilePath,
//        array $srcDirs,
//        bool $skipCoverage
//    ) {
//        $this->jUnitFilePath = $jUnitFilePath;
//        $this->srcDirs = $srcDirs;
//    }

    protected function getXmlHelper(): XmlConfigurationHelper
    {
        if ($this->xmlConfigurationHelper === null) {
            $this->setXmlHelper(new XmlConfigurationHelper());
        }

        return $this->xmlConfigurationHelper;
    }

    protected function getJUnitFilePath(): string
    {

    }



    public function setXmlHelper(XmlConfigurationHelper $xmlHelper): self
    {
        $this->xmlConfigurationHelper = $xmlHelper;

        return $this;
    }

    public function build(): string
    {
        $path = $this->buildPath();

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->readConfigFile());

        $xPath = new \DOMXPath($dom);

        $this->addCoverageFilterWhitelistIfDoesNotExist($dom, $xPath);
        $this->getXmlHelper()->replaceWithAbsolutePaths($xPath);
        $this->getXmlHelper()->setStopOnFailure($xPath);
        $this->getXmlHelper()->deactivateColours($xPath);
        $this->getXmlHelper()->removeExistingLoggers($dom, $xPath);
        $this->getXmlHelper()->removeExistingPrinters($dom, $xPath);

        if (!$this->canSkipCoverage()) {
            $this->addCodeCoverageLogger($dom, $xPath);
            $this->addJUnitLogger($dom, $xPath);
        }

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function buildPath(): string
    {
        return $this->getTempDirectory() . '/phpunitConfiguration.initial.infection.xml';
    }

    private function addJUnitLogger(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $junitLog = $dom->createElement('log');
        $junitLog->setAttribute('type', 'junit');
        $junitLog->setAttribute('target', $this->jUnitFilePath);

        $logging->appendChild($junitLog);
    }

    private function addCodeCoverageLogger(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $logging = $this->getOrCreateNode($dom, $xPath, 'logging');

        $coverageXmlLog = $dom->createElement('log');
        $coverageXmlLog->setAttribute('type', 'coverage-xml');
        $coverageXmlLog->setAttribute('target', $this->getTempDirectory() . '/' . CodeCoverageData::PHP_UNIT_COVERAGE_DIR);

        $logging->appendChild($coverageXmlLog);
    }

    private function addCoverageFilterWhitelistIfDoesNotExist(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $filterNode = $this->getNode($dom, $xPath, 'filter');

        if (!$filterNode) {
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

        if (!$node) {
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
