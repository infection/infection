<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Builder;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\Coverage\TestLocation;
use _HumbugBox9658796bb9f0\Infection\StreamWrapper\IncludeInterceptor;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use _HumbugBox9658796bb9f0\Infection\TestFramework\SafeDOMXPath;
use function _HumbugBox9658796bb9f0\Safe\file_put_contents;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
class MutationConfigBuilder extends ConfigBuilder
{
    private ?string $originalBootstrapFile = null;
    private ?DOMDocument $dom = null;
    public function __construct(private string $tmpDir, private string $originalXmlConfigContent, private XmlConfigurationManipulator $configManipulator, private string $projectDir, private JUnitTestCaseSorter $jUnitTestCaseSorter)
    {
    }
    public function build(array $tests, string $mutantFilePath, string $mutationHash, string $mutationOriginalFilePath, string $version) : string
    {
        $dom = $this->getDom();
        $xPath = new SafeDOMXPath($dom);
        $this->configManipulator->replaceWithAbsolutePaths($xPath);
        $originalBootstrapFile = $this->originalBootstrapFile;
        if ($originalBootstrapFile === null) {
            $originalBootstrapFile = $this->originalBootstrapFile = $this->getOriginalBootstrapFilePath($xPath);
        }
        $this->configManipulator->handleResultCacheAndExecutionOrder($version, $xPath, $mutationHash);
        $this->configManipulator->addFailOnAttributesIfNotSet($version, $xPath);
        $this->configManipulator->setStopOnFailure($xPath);
        $this->configManipulator->deactivateColours($xPath);
        $this->configManipulator->deactivateStderrRedirection($xPath);
        $this->configManipulator->removeExistingLoggers($xPath);
        $this->configManipulator->removeExistingPrinters($xPath);
        $this->configManipulator->removeDefaultTestSuite($xPath);
        $customAutoloadFilePath = sprintf('%s/interceptor.autoload.%s.infection.php', $this->tmpDir, $mutationHash);
        $this->setCustomBootstrapPath($customAutoloadFilePath, $xPath);
        $this->setFilteredTestsToRun($tests, $dom, $xPath);
        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutationOriginalFilePath, $mutantFilePath, $originalBootstrapFile));
        $path = $this->buildPath($mutationHash);
        file_put_contents($path, $dom->saveXML());
        return $path;
    }
    private function getDom() : DOMDocument
    {
        if ($this->dom !== null) {
            return $this->dom;
        }
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = \false;
        $dom->formatOutput = \true;
        $success = @$dom->loadXML($this->originalXmlConfigContent);
        Assert::true($success);
        return $this->dom = $dom;
    }
    private function createCustomAutoloadWithInterceptor(string $originalFilePath, string $mutantFilePath, string $originalAutoloadFile) : string
    {
        $interceptorPath = IncludeInterceptor::LOCATION;
        return sprintf(<<<'PHP'
<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}
%s
require_once '%s';

PHP
, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath), $originalAutoloadFile);
    }
    private function buildPath(string $mutationHash) : string
    {
        return sprintf('%s/phpunitConfiguration.%s.infection.xml', $this->tmpDir, $mutationHash);
    }
    private function setCustomBootstrapPath(string $customAutoloadFilePath, SafeDOMXPath $xPath) : void
    {
        $bootstrap = $xPath->query('/phpunit/@bootstrap');
        if ($bootstrap->length > 0) {
            $bootstrap[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('bootstrap', $customAutoloadFilePath);
        }
    }
    private function setFilteredTestsToRun(array $tests, DOMDocument $dom, SafeDOMXPath $xPath) : void
    {
        $this->removeExistingTestSuite($xPath);
        $this->addTestSuiteWithFilteredTestFiles($tests, $dom, $xPath);
    }
    private function removeExistingTestSuite(SafeDOMXPath $xPath) : void
    {
        $this->removeExistingTestSuiteNodes($xPath->query('/phpunit/testsuites/testsuite'));
        $this->removeExistingTestSuiteNodes($xPath->query('/phpunit/testsuite'));
    }
    private function removeExistingTestSuiteNodes(DOMNodeList $testSuites) : void
    {
        foreach ($testSuites as $node) {
            $parent = $node->parentNode;
            Assert::isInstanceOf($parent, DOMNode::class);
            $parent->removeChild($node);
        }
    }
    private function addTestSuiteWithFilteredTestFiles(array $tests, DOMDocument $dom, SafeDOMXPath $xPath) : void
    {
        $testSuites = $xPath->query('/phpunit/testsuites');
        $nodeToAppendTestSuite = $testSuites->item(0);
        if ($nodeToAppendTestSuite === null) {
            $nodeToAppendTestSuite = $xPath->query('/phpunit')->item(0);
        }
        $testSuite = $dom->createElement('testsuite');
        $testSuite->setAttribute('name', 'Infection testsuite with filtered tests');
        $uniqueTestFilePaths = $this->jUnitTestCaseSorter->getUniqueSortedFileNames($tests);
        foreach ($uniqueTestFilePaths as $testFilePath) {
            $file = $dom->createElement('file', $testFilePath);
            $testSuite->appendChild($file);
        }
        Assert::isInstanceOf($nodeToAppendTestSuite, DOMNode::class);
        $nodeToAppendTestSuite->appendChild($testSuite);
    }
    private function getOriginalBootstrapFilePath(SafeDOMXPath $xPath) : string
    {
        $bootstrap = $xPath->query('/phpunit/@bootstrap');
        if ($bootstrap->length > 0) {
            return $bootstrap[0]->nodeValue;
        }
        return sprintf('%s/vendor/autoload.php', $this->projectDir);
    }
}
