<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;

class InitialXmlConfiguration extends AbstractXmlConfiguration
{
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
        $this->addLogger($dom, $xPath);

        return $dom->saveXML();
    }
}