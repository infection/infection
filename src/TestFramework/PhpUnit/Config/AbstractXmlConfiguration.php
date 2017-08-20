<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

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

    abstract public function getXml(): string;

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
