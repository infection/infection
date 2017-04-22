<?php

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;


use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class InitialXmlConfiguration
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var string
     */
    private $originalXmlConfigPath;

    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer)
    {
        $this->tempDirectory = $tempDirectory;
        $this->originalXmlConfigPath = $originalXmlConfigPath;
        $this->pathReplacer = $pathReplacer;
    }

    public function getXml() : string
    {
        $originalXml = file_get_contents($this->originalXmlConfigPath);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($originalXml);

        $xPath = new \DOMXPath($dom);

        $this->replaceWithAbsolutePaths($dom, $xPath);
        $this->removeExistingLoggers($dom, $xPath);
        $this->addLogger($dom, $xPath);

        return $dom->saveXML();
    }

    private function replaceWithAbsolutePaths(\DOMDocument $dom, \DOMXPath $xPath)
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

    private function removeExistingLoggers(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $nodes = $xPath->query('/phpunit/logging');

        foreach ($nodes as $node) {
            $dom->documentElement->removeChild($node);
        }
    }

    private function addLogger(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $loggingList = $xPath->query('/phpunit/logging');

        if ($loggingList->length) {
            $logging = $loggingList->item(0);
        } else {
            $logging = $dom->createElement('logging');
            $dom->documentElement->appendChild($logging);
        }

        $log = $dom->createElement('log');
        $log->setAttribute('type', 'coverage-php');
        $log->setAttribute('target', $this->tempDirectory . '/coverage.infection.php');

        $logging->appendChild($log);
    }
}