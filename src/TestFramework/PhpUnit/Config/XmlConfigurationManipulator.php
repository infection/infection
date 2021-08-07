<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config;

use DOMDocument;
use DOMElement;
use const FILTER_VALIDATE_URL;
use function filter_var;
use function implode;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\SafeDOMXPath;
use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use LibXMLError;
use LogicException;
use function Safe\sprintf;
use function version_compare;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class XmlConfigurationManipulator
{
    private PathReplacer $pathReplacer;
    private string $phpUnitConfigDir;

    public function __construct(PathReplacer $pathReplacer, string $phpUnitConfigDir)
    {
        $this->pathReplacer = $pathReplacer;
        $this->phpUnitConfigDir = $phpUnitConfigDir;
    }

    public function replaceWithAbsolutePaths(SafeDOMXPath $xPath): void
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

    /**
     * Removes existing loggers to improve throughput during MT. Initial test loggers are added through CLI arguments.
     */
    public function removeExistingLoggers(SafeDOMXPath $xPath): void
    {
        foreach ($xPath->query('/phpunit/logging') as $node) {
            $node->parentNode->removeChild($node);
        }

        foreach ($xPath->query('/phpunit/coverage/report') as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    public function deactivateResultCaching(SafeDOMXPath $xPath): void
    {
        $this->setAttributeValue($xPath, 'cacheResult', 'false');
    }

    public function setDefaultTestsOrderAttribute(string $version, SafeDOMXPath $xPath): void
    {
        if (version_compare($version, '7.2', '<')) {
            return;
        }

        $this->setAttributeValue($xPath, 'executionOrder', 'default');
    }

    public function deactivateStderrRedirection(SafeDOMXPath $xPath): void
    {
        $this->setAttributeValue($xPath, 'stderr', 'false');
    }

    public function setStopOnFailure(SafeDOMXPath $xPath): void
    {
        $this->setAttributeValue(
            $xPath,
            'stopOnFailure',
            'true'
        );
    }

    public function deactivateColours(SafeDOMXPath $xPath): void
    {
        $this->setAttributeValue(
            $xPath,
            'colors',
            'false'
        );
    }

    public function removeExistingPrinters(SafeDOMXPath $xPath): void
    {
        $this->removeAttribute(
            $xPath,
            'printerClass'
        );
    }

    /**
     * @param string[] $srcDirs
     * @param list<string> $filteredSourceFilesToMutate
     */
    public function addOrUpdateLegacyCoverageWhitelistNodes(SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate): void
    {
        $this->addOrUpdateCoverageNodes('filter', 'whitelist', $xPath, $srcDirs, $filteredSourceFilesToMutate);
    }

    /**
     * @param string[] $srcDirs
     * @param list<string> $filteredSourceFilesToMutate
     */
    public function addOrUpdateCoverageIncludeNodes(SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate): void
    {
        $this->addOrUpdateCoverageNodes('coverage', 'include', $xPath, $srcDirs, $filteredSourceFilesToMutate);
    }

    public function validate(string $configPath, SafeDOMXPath $xPath): bool
    {
        if ($xPath->query('/phpunit')->length === 0) {
            throw InvalidPhpUnitConfiguration::byRootNode($configPath);
        }

        if ($xPath->query('namespace::xsi')->length === 0) {
            return true;
        }

        $schema = $xPath->query('/phpunit/@xsi:noNamespaceSchemaLocation');

        $original = libxml_use_internal_errors(true);

        if ($schema->length && !$xPath->document->schemaValidate($this->buildSchemaPath($schema[0]->nodeValue))) {
            throw InvalidPhpUnitConfiguration::byXsdSchema(
                $configPath,
                $this->getXmlErrorsString()
            );
        }

        libxml_use_internal_errors($original);

        return true;
    }

    public function removeDefaultTestSuite(SafeDOMXPath $xPath): void
    {
        $this->removeAttribute(
            $xPath,
            'defaultTestSuite'
        );
    }

    /**
     * @param string[] $srcDirs
     * @param list<string> $filteredSourceFilesToMutate
     */
    private function addOrUpdateCoverageNodes(string $parentName, string $listName, SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate): void
    {
        $coverageNodeExists = $this->nodeExists($xPath, "{$parentName}/{$listName}");

        if ($coverageNodeExists) {
            if ($filteredSourceFilesToMutate === []) {
                // use original phpunit.xml's coverage setting since all files need to be mutated (no filter is set)
                return;
            }

            $this->removeCoverageChildNode($xPath, "{$parentName}/{$listName}");
        }

        $filterNode = $this->getOrCreateNode($xPath, $xPath->document, $parentName);

        $listNode = $xPath->document->createElement($listName);

        if ($filteredSourceFilesToMutate === []) {
            foreach ($srcDirs as $srcDir) {
                $directoryNode = $xPath->document->createElement(
                    'directory',
                    $srcDir
                );

                $listNode->appendChild($directoryNode);
            }
        } else {
            foreach ($filteredSourceFilesToMutate as $sourceFileRealPath) {
                $directoryNode = $xPath->document->createElement(
                    'file',
                    $sourceFileRealPath
                );

                $listNode->appendChild($directoryNode);
            }
        }

        $filterNode->appendChild($listNode);
    }

    private function nodeExists(SafeDOMXPath $xPath, string $nodeName): bool
    {
        return $xPath->query(sprintf('/phpunit/%s', $nodeName))->length > 0;
    }

    private function createNode(DOMDocument $dom, string $nodeName): DOMElement
    {
        $node = $dom->createElement($nodeName);
        $document = $dom->documentElement;

        Assert::isInstanceOf($document, DOMElement::class);
        $document->appendChild($node);

        return $node;
    }

    private function getXmlErrorsString(): string
    {
        $errorsString = '';
        $errors = libxml_get_errors();

        foreach ($errors as $error) {
            $level = $this->getErrorLevelName($error);
            $errorsString .= sprintf('[%s] %s', $level, $error->message);

            if ($error->file) {
                $errorsString .= sprintf(' in %s (line %s, col %s)', $error->file, $error->line, $error->column);
            }

            $errorsString .= "\n";
        }

        return $errorsString;
    }

    private function buildSchemaPath(string $nodeValue): string
    {
        if (filter_var($nodeValue, FILTER_VALIDATE_URL)) {
            return $nodeValue;
        }

        if ($this->phpUnitConfigDir === '') {
            $schemaPath = $nodeValue;
        } else {
            $schemaPath = sprintf('%s/%s', $this->phpUnitConfigDir, $nodeValue);
        }

        Assert::fileExists($schemaPath, 'Invalid schema path found %s');

        return $schemaPath;
    }

    private function removeAttribute(SafeDOMXPath $xPath, string $name): void
    {
        $nodeList = $xPath->query(sprintf(
            '/phpunit/@%s',
            $name
        ));

        if ($nodeList->length > 0) {
            $document = $xPath->document->documentElement;
            Assert::isInstanceOf($document, DOMElement::class);
            $document->removeAttribute($name);
        }
    }

    private function setAttributeValue(SafeDOMXPath $xPath, string $name, string $value): void
    {
        $nodeList = $xPath->query(sprintf(
            '/phpunit/@%s',
            $name
        ));

        if ($nodeList->length > 0) {
            $nodeList[0]->nodeValue = $value;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($name, $value);
        }
    }

    private function getErrorLevelName(LibXMLError $error): string
    {
        if ($error->level === LIBXML_ERR_WARNING) {
            return 'Warning';
        }

        if ($error->level === LIBXML_ERR_ERROR) {
            return 'Error';
        }

        if ($error->level === LIBXML_ERR_FATAL) {
            return 'Fatal';
        }

        throw new LogicException(sprintf('Unknown lib XML error level "%s"', $error->level));
    }

    private function removeCoverageChildNode(SafeDOMXPath $xPath, string $nodeQuery): void
    {
        foreach ($xPath->query($nodeQuery) as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function getOrCreateNode(SafeDOMXPath $xPath, DOMDocument $dom, string $nodeName): DOMElement
    {
        $node = $xPath->query(sprintf('/phpunit/%s', $nodeName));

        if ($node->length > 0) {
            return $node[0];
        }

        return $this->createNode($dom, $nodeName);
    }
}
