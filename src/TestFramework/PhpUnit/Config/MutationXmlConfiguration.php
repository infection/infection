<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class MutationXmlConfiguration extends AbstractXmlConfiguration
{
    /**
     * @var string
     */
    private $customAutoloadFilePath;

    /**
     * @var array
     */
    private $coveredTests;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer, string $customAutoloadFilePath, array $coveredTests)
    {
        parent::__construct($tempDirectory, $originalXmlConfigPath, $pathReplacer);

        $this->customAutoloadFilePath = $customAutoloadFilePath;
        $this->coveredTests = $coveredTests;
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
        $this->setCustomAutoLoaderPath($xPath);
        $this->setStopOnFailure($xPath);
        $this->deactivateColours($xPath);
        $this->removeExistingLoggers($dom, $xPath);
        $this->setFilteredTestsToRun($dom, $xPath);

        return $dom->saveXML();
    }

    private function setCustomAutoLoaderPath(\DOMXPath $xPath)
    {
        $node = $xPath->query('/phpunit/@bootstrap')[0];

        $node->nodeValue = $this->customAutoloadFilePath;
    }

    private function setFilteredTestsToRun($dom, $xPath)
    {
        $nodes = $xPath->query('/phpunit/testsuites/testsuite');

        foreach ($nodes as $node) {
            $dom->documentElement->removeChild($node);
        }

        $loggingList = $xPath->query('/phpunit/testsuites');

        $testsuites = $loggingList->item(0);

        $testsuite = $dom->createElement('testsuite');
        $testsuite->setAttribute('name', 'Infection testsuite with filtered tests');

        $testsuites->appendChild($testsuite);
    }
}