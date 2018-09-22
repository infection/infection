<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

/**
 * @internal
 */
final class XmlConfigurationHelper
{
    /**
     * @var PathReplacer
     */
    private $pathReplacer;

    public function __construct(PathReplacer $pathReplacer)
    {
        $this->pathReplacer = $pathReplacer;
    }

    public function replaceWithAbsolutePaths(\DOMXPath $xPath): void
    {
        $queries = [
            '/phpunit/@bootstrap',
            '/phpunit/testsuites/testsuite/exclude',
            '//directory',
            '//file',
        ];

        foreach ($xPath->query(implode('|', $queries)) as $node) {
            $this->pathReplacer->replaceInNode($node);
        }
    }

    public function removeExistingLoggers(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        foreach ($xPath->query('/phpunit/logging') as $node) {
            $dom->documentElement->removeChild($node);
        }
    }

    public function setStopOnFailure(\DOMXPath $xPath): void
    {
        $nodeList = $xPath->query('/phpunit/@stopOnFailure');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'true';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('stopOnFailure', 'true');
        }
    }

    public function deactivateColours(\DOMXPath $xPath): void
    {
        $nodeList = $xPath->query('/phpunit/@colors');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = 'false';
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('colors', 'false');
        }
    }

    public function removeExistingPrinters(\DOMDocument $dom, \DOMXPath $xPath): void
    {
        $nodeList = $xPath->query('/phpunit/@printerClass');

        if ($nodeList->length) {
            $dom->documentElement->removeAttribute('printerClass');
        }
    }

    public function validate(\DOMDocument $dom, \DOMXPath $xPath): bool
    {
        if ($xPath->query('/phpunit')->length === 0) {
            throw InvalidPhpUnitXmlConfigException::byRootNode();
        }

        if (!$xPath->query('namespace::xsi')->length) {
            return true;
        }

        $schema = $xPath->query('/phpunit/@xsi:noNamespaceSchemaLocation');

        libxml_use_internal_errors(true);

        if ($schema->length && !$dom->schemaValidate($schema[0]->nodeValue)) {
            throw InvalidPhpUnitXmlConfigException::byXsdSchema($this->getXmlErrorsString());
        }

        return true;
    }

    private function getXmlErrorsString(): string
    {
        $errorsString = '';
        $errors = libxml_get_errors();

        foreach ($errors as $key => $error) {
            $level = $error->level === LIBXML_ERR_WARNING ? 'Warning' : $error->level === LIBXML_ERR_ERROR ? 'Error' : 'Fatal';
            $errorsString .= sprintf('[%s] %s', $level, $error->message);

            if ($error->file) {
                $errorsString .= sprintf(' in %s (line %s, col %s)', $error->file, $error->line, $error->column);
            }

            $errorsString .= "\n";
        }

        return $errorsString;
    }
}
