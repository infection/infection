<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use Infection\Mutant\MutantInterface;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;

/**
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    /**
     * @var string
     */
    private $tempDirectory;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var XmlConfigurationHelper
     */
    private $xmlConfigurationHelper;

    /**
     * @var string
     */
    private $originalXmlConfigContent;

    /**
     * @var \DOMDocument
     */
    private $dom;

    public function __construct(string $tempDirectory, string $originalXmlConfigContent, XmlConfigurationHelper $xmlConfigurationHelper, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->projectDir = $projectDir;

        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->originalXmlConfigContent = $originalXmlConfigContent;

        $this->dom = new \DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        $this->dom->loadXML($this->originalXmlConfigContent);
    }

    public function build(MutantInterface $mutant): string
    {
        // clone the dom document because it's mutated later
        $dom = clone $this->dom;

        $xPath = new \DOMXPath($dom);

        $this->xmlConfigurationHelper->replaceWithAbsolutePaths($xPath);
        $this->xmlConfigurationHelper->setStopOnFailure($xPath);
        $this->xmlConfigurationHelper->deactivateColours($xPath);
        $this->xmlConfigurationHelper->removeExistingLoggers($dom, $xPath);
        $this->xmlConfigurationHelper->removeExistingPrinters($dom, $xPath);

        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        $originalAutoloadFile = $this->getOriginalBootstrapFilePath($xPath);

        $this->setCustomBootstrapPath($customAutoloadFilePath, $xPath);
        $this->setFilteredTestsToRun($mutant->getCoverageTests(), $dom, $xPath);

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant, $originalAutoloadFile));

        $path = $this->buildPath($mutant);

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(MutantInterface $mutant, string $originalAutoloadFile): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = \dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $customAutoload = <<<AUTOLOAD
<?php

%s
require_once '{$originalAutoloadFile}';

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }

    private function buildPath(MutantInterface $mutant): string
    {
        $fileName = sprintf('phpunitConfiguration.%s.infection.xml', $mutant->getMutation()->getHash());

        return $this->tempDirectory . '/' . $fileName;
    }

    private function setCustomBootstrapPath(string $customAutoloadFilePath, \DOMXPath $xPath)
    {
        $nodeList = $xPath->query('/phpunit/@bootstrap');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('bootstrap', $customAutoloadFilePath);
        }
    }

    private function setFilteredTestsToRun(array $coverageTests, \DOMDocument $dom, \DOMXPath $xPath)
    {
        $this->removeExistingTestSuite($xPath);

        $this->addTestSuiteWithFilteredTestFiles($coverageTests, $dom, $xPath);
    }

    private function removeExistingTestSuite(\DOMXPath $xPath)
    {
        $nodes = $xPath->query('/phpunit/testsuites/testsuite');

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        // handle situation when test suite is directly inside root node
        $nodes = $xPath->query('/phpunit/testsuite');

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function addTestSuiteWithFilteredTestFiles(array $coverageTests, \DOMDocument $dom, \DOMXPath $xPath)
    {
        $testSuites = $xPath->query('/phpunit/testsuites');
        $nodeToAppendTestSuite = $testSuites->item(0);

        // if there is no `testsuites` node, append to root
        if (!$nodeToAppendTestSuite) {
            $nodeToAppendTestSuite = $testSuites = $xPath->query('/phpunit')->item(0);
        }

        $testSuite = $dom->createElement('testsuite');
        $testSuite->setAttribute('name', 'Infection testsuite with filtered tests');

        $uniqueCoverageTests = $this->unique($coverageTests);

        // sort tests to run the fastest first
        usort(
            $uniqueCoverageTests,
            function (array $a, array $b) {
                return $a['time'] <=> $b['time'];
            }
        );

        $uniqueTestFilePaths = array_column($uniqueCoverageTests, 'testFilePath');

        foreach ($uniqueTestFilePaths as $testFilePath) {
            $file = $dom->createElement('file', $testFilePath);

            $testSuite->appendChild($file);
        }

        \assert($nodeToAppendTestSuite instanceof \DOMNode);

        $nodeToAppendTestSuite->appendChild($testSuite);
    }

    private function unique(array $coverageTests): array
    {
        $usedFileNames = [];
        $uniqueTests = [];

        foreach ($coverageTests as $coverageTest) {
            if (!\in_array($coverageTest['testFilePath'], $usedFileNames, true)) {
                $uniqueTests[] = $coverageTest;
                $usedFileNames[] = $coverageTest['testFilePath'];
            }
        }

        return $uniqueTests;
    }

    private function getOriginalBootstrapFilePath(\DOMXPath $xPath): string
    {
        $nodeList = $xPath->query('/phpunit/@bootstrap');

        if ($nodeList->length) {
            return $nodeList[0]->nodeValue;
        }

        return sprintf('%s/vendor/autoload.php', $this->projectDir);
    }
}
