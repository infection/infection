<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types = 1);


namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialXmlConfiguration extends AbstractXmlConfiguration
{
    /**
     * @var string
     */
    private $jUnitFilePath;

    public function __construct($tempDirectory, $originalXmlConfigPath, PathReplacer $pathReplacer, string $jUnitFilePath)
    {
        parent::__construct($tempDirectory, $originalXmlConfigPath, $pathReplacer);

        $this->jUnitFilePath = $jUnitFilePath;
    }

    public function getXml() : string
    {
        $originalXml = file_get_contents($this->originalXmlConfigPath);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($originalXml);

        $xPath = new \DOMXPath($dom);

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
        $loggingList = $xPath->query('/phpunit/logging');

        if ($loggingList->length) {
            $logging = $loggingList->item(0);
        } else {
            $logging = $dom->createElement('logging');
            $dom->documentElement->appendChild($logging);
        }

        $junitLog = $dom->createElement('log');
        $junitLog->setAttribute('type', 'junit');
        $junitLog->setAttribute('target', $this->jUnitFilePath);

        $logging->appendChild($junitLog);
    }
}