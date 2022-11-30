<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config;

use DOMDocument;
use DOMElement;
use const FILTER_VALIDATE_URL;
use function filter_var;
use function implode;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use const LIBXML_ERR_ERROR;
use const LIBXML_ERR_FATAL;
use const LIBXML_ERR_WARNING;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use LibXMLError;
use LogicException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function version_compare;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class XmlConfigurationManipulator
{
    public function __construct(private PathReplacer $pathReplacer, private string $phpUnitConfigDir)
    {
    }
    public function replaceWithAbsolutePaths(SafeDOMXPath $xPath) : void
    {
        $queries = ['/phpunit/@bootstrap', '/phpunit/testsuites/testsuite/exclude', '//directory', '//file'];
        foreach ($xPath->query(implode('|', $queries)) as $node) {
            $this->pathReplacer->replaceInNode($node);
        }
    }
    public function removeExistingLoggers(SafeDOMXPath $xPath) : void
    {
        foreach ($xPath->query('/phpunit/logging') as $node) {
            $node->parentNode->removeChild($node);
        }
        foreach ($xPath->query('/phpunit/coverage/report') as $node) {
            $node->parentNode->removeChild($node);
        }
    }
    public function deactivateResultCaching(SafeDOMXPath $xPath) : void
    {
        $this->setAttributeValue($xPath, 'cacheResult', 'false');
    }
    public function handleResultCacheAndExecutionOrder(string $version, SafeDOMXPath $xPath, string $mutationHash) : void
    {
        if (version_compare($version, '7.3', '>=')) {
            $this->setAttributeValue($xPath, 'cacheResult', 'true');
            $this->setAttributeValue($xPath, 'cacheResultFile', sprintf('.phpunit.result.cache.%s', $mutationHash));
            $this->setAttributeValue($xPath, 'executionOrder', 'defects');
            return;
        }
        if (version_compare($version, '7.2', '>=')) {
            $this->setAttributeValue($xPath, 'executionOrder', 'default');
        }
    }
    public function deactivateStderrRedirection(SafeDOMXPath $xPath) : void
    {
        $this->setAttributeValue($xPath, 'stderr', 'false');
    }
    public function setStopOnFailure(SafeDOMXPath $xPath) : void
    {
        $this->setAttributeValue($xPath, 'stopOnFailure', 'true');
    }
    public function deactivateColours(SafeDOMXPath $xPath) : void
    {
        $this->setAttributeValue($xPath, 'colors', 'false');
    }
    public function removeExistingPrinters(SafeDOMXPath $xPath) : void
    {
        $this->removeAttribute($xPath, 'printerClass');
    }
    public function addOrUpdateLegacyCoverageWhitelistNodes(SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate) : void
    {
        $this->addOrUpdateCoverageNodes('filter', 'whitelist', $xPath, $srcDirs, $filteredSourceFilesToMutate);
    }
    public function addOrUpdateCoverageIncludeNodes(SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate) : void
    {
        $this->addOrUpdateCoverageNodes('coverage', 'include', $xPath, $srcDirs, $filteredSourceFilesToMutate);
    }
    public function validate(string $configPath, SafeDOMXPath $xPath) : bool
    {
        if ($xPath->query('/phpunit')->length === 0) {
            throw InvalidPhpUnitConfiguration::byRootNode($configPath);
        }
        if ($xPath->query('namespace::xsi')->length === 0) {
            return \true;
        }
        $schema = $xPath->query('/phpunit/@xsi:noNamespaceSchemaLocation');
        $original = libxml_use_internal_errors(\true);
        if ($schema->length > 0 && !$xPath->document->schemaValidate($this->buildSchemaPath($schema[0]->nodeValue))) {
            throw InvalidPhpUnitConfiguration::byXsdSchema($configPath, $this->getXmlErrorsString());
        }
        libxml_use_internal_errors($original);
        return \true;
    }
    public function removeDefaultTestSuite(SafeDOMXPath $xPath) : void
    {
        $this->removeAttribute($xPath, 'defaultTestSuite');
    }
    public function addFailOnAttributesIfNotSet(string $version, SafeDOMXPath $xPath) : void
    {
        if (version_compare($version, '5.2', '<')) {
            return;
        }
        $this->addAttributeIfNotSet('failOnRisky', 'true', $xPath);
        $this->addAttributeIfNotSet('failOnWarning', 'true', $xPath);
    }
    private function addOrUpdateCoverageNodes(string $parentName, string $listName, SafeDOMXPath $xPath, array $srcDirs, array $filteredSourceFilesToMutate) : void
    {
        $coverageNodeExists = $this->nodeExists($xPath, "{$parentName}/{$listName}");
        if ($coverageNodeExists) {
            if ($filteredSourceFilesToMutate === []) {
                return;
            }
            $this->removeCoverageChildNode($xPath, "{$parentName}/{$listName}");
        }
        $filterNode = $this->getOrCreateNode($xPath, $xPath->document, $parentName);
        $listNode = $xPath->document->createElement($listName);
        if ($filteredSourceFilesToMutate === []) {
            foreach ($srcDirs as $srcDir) {
                $directoryNode = $xPath->document->createElement('directory', $srcDir);
                $listNode->appendChild($directoryNode);
            }
        } else {
            foreach ($filteredSourceFilesToMutate as $sourceFileRealPath) {
                $directoryNode = $xPath->document->createElement('file', $sourceFileRealPath);
                $listNode->appendChild($directoryNode);
            }
        }
        $filterNode->appendChild($listNode);
    }
    private function nodeExists(SafeDOMXPath $xPath, string $nodeName) : bool
    {
        return $xPath->query(sprintf('/phpunit/%s', $nodeName))->length > 0;
    }
    private function createNode(DOMDocument $dom, string $nodeName) : DOMElement
    {
        $node = $dom->createElement($nodeName);
        $document = $dom->documentElement;
        Assert::isInstanceOf($document, DOMElement::class);
        $document->appendChild($node);
        return $node;
    }
    private function getXmlErrorsString() : string
    {
        $errorsString = '';
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            $level = $this->getErrorLevelName($error);
            $errorsString .= sprintf('[%s] %s', $level, $error->message);
            if ($error->file !== '') {
                $errorsString .= sprintf(' in %s (line %s, col %s)', $error->file, $error->line, $error->column);
            }
            $errorsString .= "\n";
        }
        return $errorsString;
    }
    private function buildSchemaPath(string $nodeValue) : string
    {
        if (filter_var($nodeValue, FILTER_VALIDATE_URL) !== \false) {
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
    private function removeAttribute(SafeDOMXPath $xPath, string $name) : void
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $name));
        if ($nodeList->length > 0) {
            $document = $xPath->document->documentElement;
            Assert::isInstanceOf($document, DOMElement::class);
            $document->removeAttribute($name);
        }
    }
    private function setAttributeValue(SafeDOMXPath $xPath, string $name, string $value) : void
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $name));
        if ($nodeList->length > 0) {
            $nodeList[0]->nodeValue = $value;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($name, $value);
        }
    }
    private function getErrorLevelName(LibXMLError $error) : string
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
    private function removeCoverageChildNode(SafeDOMXPath $xPath, string $nodeQuery) : void
    {
        foreach ($xPath->query($nodeQuery) as $node) {
            $node->parentNode->removeChild($node);
        }
    }
    private function getOrCreateNode(SafeDOMXPath $xPath, DOMDocument $dom, string $nodeName) : DOMElement
    {
        $node = $xPath->query(sprintf('/phpunit/%s', $nodeName));
        if ($node->length > 0) {
            return $node[0];
        }
        return $this->createNode($dom, $nodeName);
    }
    private function addAttributeIfNotSet(string $attribute, string $value, SafeDOMXPath $xPath) : bool
    {
        $nodeList = $xPath->query(sprintf('/phpunit/@%s', $attribute));
        if ($nodeList->length === 0) {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute($attribute, $value);
            return \true;
        }
        return \false;
    }
}
