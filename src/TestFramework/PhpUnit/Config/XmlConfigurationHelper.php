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

use DOMElement;
use DOMXPath;
use const FILTER_VALIDATE_URL;
use function filter_var;
use function implode;
use Infection\TestFramework\PhpUnit\Config\Exception\InvalidPhpUnitXmlConfigException;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use Infection\TestFramework\SafeQuery;
use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use LibXMLError;
use LogicException;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class XmlConfigurationHelper
{
    use SafeQuery;

    private $pathReplacer;
    private $phpUnitConfigDir;

    public function __construct(PathReplacer $pathReplacer, string $phpUnitConfigDir)
    {
        $this->pathReplacer = $pathReplacer;
        $this->phpUnitConfigDir = $phpUnitConfigDir;
    }

    public function replaceWithAbsolutePaths(DOMXPath $xPath): void
    {
        $queries = [
            '/phpunit/@bootstrap',
            '/phpunit/testsuites/testsuite/exclude',
            '//directory',
            '//file',
        ];

        foreach (self::safeQuery($xPath, implode('|', $queries)) as $node) {
            $this->pathReplacer->replaceInNode($node);
        }
    }

    public function removeExistingLoggers(DOMXPath $xPath): void
    {
        foreach (self::safeQuery($xPath, '/phpunit/logging') as $node) {
            $document = $xPath->document->documentElement;
            Assert::isInstanceOf($document, DOMElement::class);
            $document->removeChild($node);
        }
    }

    public function deactivateResultCaching(DOMXPath $xPath): void
    {
        $this->setAttributeValue($xPath, 'cacheResult', 'false');
    }

    public function deactivateStderrRedirection(DOMXPath $xPath): void
    {
        $this->setAttributeValue($xPath, 'stderr', 'false');
    }

    public function setStopOnFailure(DOMXPath $xPath): void
    {
        $this->setAttributeValue(
            $xPath,
            'stopOnFailure',
            'true'
        );
    }

    public function deactivateColours(DOMXPath $xPath): void
    {
        $this->setAttributeValue(
            $xPath,
            'colors',
            'false'
        );
    }

    public function removeExistingPrinters(DOMXPath $xPath): void
    {
        $this->removeAttribute(
            $xPath,
            'printerClass'
        );
    }

    public function validate(DOMXPath $xPath): bool
    {
        if (self::safeQuery($xPath, '/phpunit')->length === 0) {
            // TODO: should have the PHPUnit config path passed otherwise we blindly assume
            //  "phpunit.xml" without neither the path neither guarantee this is the file name
            //  (it could be a different one passed with the --configuration option)
            throw InvalidPhpUnitXmlConfigException::byRootNode();
        }

        if (self::safeQuery($xPath, 'namespace::xsi')->length === 0) {
            return true;
        }

        $schema = self::safeQuery($xPath, '/phpunit/@xsi:noNamespaceSchemaLocation');

        $original = libxml_use_internal_errors(true);
        $schemaPath = $this->buildSchemaPath($schema[0]->nodeValue);

        // TODO: schemaValidate will throw a weird error if schemaPath is invalid, e.g. ''
        // check what happens with invalid URL or invalid path
        if ($schema->length && !$xPath->document->schemaValidate($schemaPath)) {
            throw InvalidPhpUnitXmlConfigException::byXsdSchema($this->getXmlErrorsString());
        }

        libxml_use_internal_errors($original);

        return true;
    }

    public function removeDefaultTestSuite(DOMXPath $xPath): void
    {
        $this->removeAttribute(
            $xPath,
            'defaultTestSuite'
        );
    }

    private function getXmlErrorsString(): string
    {
        $errorsString = '';
        $errors = libxml_get_errors();

        foreach ($errors as $key => $error) {
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
        if ($this->phpUnitConfigDir === '' || filter_var($nodeValue, FILTER_VALIDATE_URL)) {
            return $nodeValue;
        }

        return sprintf('%s/%s', $this->phpUnitConfigDir, $nodeValue);
    }

    private function removeAttribute(DOMXPath $xPath, string $name): void
    {
        $nodeList = self::safeQuery($xPath, sprintf(
            '/phpunit/@%s',
            $name
        ));

        if ($nodeList->length) {
            $document = $xPath->document->documentElement;
            Assert::isInstanceOf($document, DOMElement::class);
            $document->removeAttribute($name);
        }
    }

    private function setAttributeValue(DOMXPath $xPath, string $name, string $value): void
    {
        $nodeList = self::safeQuery($xPath, sprintf(
            '/phpunit/@%s',
            $name
        ));

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = $value;
        } else {
            $node = self::safeQuery($xPath, '/phpunit')[0];
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
}
