<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\Config\InfectionConfig;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class XmlConfigurationHelper
{
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    public function __construct(PathReplacer $pathReplacer)
    {
        $this->pathReplacer = $pathReplacer;
    }

    public function replaceWithAbsolutePaths(\DOMXPath $xPath)
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

    public function removeExistingLoggers(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $nodes = $xPath->query('/phpunit/logging');

        foreach ($nodes as $node) {
            $dom->documentElement->removeChild($node);
        }
    }

    public function setStopOnFailure(\DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@stopOnFailure');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'true';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('stopOnFailure', 'true');
        }
    }

    public function deactivateColours(\DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@colors');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'false';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('colors', 'false');
        }
    }

    public function removeExistingPrinters(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@printerClass');

        if ($nodeList->length) {
            $dom->documentElement->removeAttribute('printerClass');
        }
    }

    public function addMemoryLimit(\DOMXPath $xPath, \DOMDocument $dom)
    {
        if ($xPath->query('/phpunit/php/ini[@name="memory_limit"]')->length) {
            return;
        }

        $nodeList = $xPath->query('/phpunit/php');

        if ($nodeList->length) {
            $node = $nodeList[0];
        } else {
            $node = $dom->createElement('php');
            $xPath->query('/phpunit')[0]->appendChild($node);
        }

        $element = $dom->createElement('ini');
        $element->setAttribute('name', 'memory_limit');
        $element->setAttribute('value', InfectionConfig::PROCESS_MEMORY_LIMIT);
        $node->appendChild($element);
    }
}
