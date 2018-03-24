<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;

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
     * @var \DOMDocument
     */
    private $dom;

    /**
     * @var \DOMXPath
     */
    private $xPath;

    public function __construct(string $tempDirectory, string $originalXmlConfigContent, XmlConfigurationHelper $xmlConfigurationHelper, string $projectDir)
    {
        $this->tempDirectory = $tempDirectory;
        $this->projectDir = $projectDir;

        $this->dom = new \DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        $this->dom->loadXML($originalXmlConfigContent);

        $this->xPath = new \DOMXPath($this->dom);

        $xmlConfigurationHelper->replaceWithAbsolutePaths($this->xPath);
        $xmlConfigurationHelper->setStopOnFailure($this->xPath);
        $xmlConfigurationHelper->deactivateColours($this->xPath);
        $xmlConfigurationHelper->removeExistingLoggers($this->dom, $this->xPath);
        $xmlConfigurationHelper->removeExistingPrinters($this->dom, $this->xPath);
        $xmlConfigurationHelper->addMemoryLimit($this->xPath, $this->dom);
    }

    public function build(Mutant $mutant): string
    {
        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutant->getMutation()->getHash()
        );

        $this->setCustomAutoLoaderPath($customAutoloadFilePath);
        $this->setFilteredTestsToRun($mutant->getCoverageTests());

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutant));

        $path = $this->buildPath($mutant);

        file_put_contents($path, $this->dom->saveXML());

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(Mutant $mutant): string
    {
        $originalFilePath = $mutant->getMutation()->getOriginalFilePath();
        $mutatedFilePath = $mutant->getMutatedFilePath();
        $interceptorPath = dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        // TODO change to what it was (e.g. app/autoload - see simplehabits)
        $autoload = sprintf('%s/vendor/autoload.php', $this->projectDir);

        $customAutoload = <<<AUTOLOAD
<?php

require_once '{$autoload}';
%s

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }

    private function buildPath(Mutant $mutant): string
    {
        $fileName = sprintf('phpunitConfiguration.%s.infection.xml', $mutant->getMutation()->getHash());

        return $this->tempDirectory . '/' . $fileName;
    }

    private function setCustomAutoLoaderPath(string $customAutoloadFilePath)
    {
        $nodeList = $this->xPath->query('/phpunit/@bootstrap');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = $this->xPath->query('/phpunit')[0];
            $node->setAttribute('bootstrap', $customAutoloadFilePath);
        }
    }

    private function setFilteredTestsToRun(array $coverageTests)
    {
        $this->removeExistingTestSuite();

        $this->addTestSuiteWithFilteredTestFiles($coverageTests);
    }

    private function removeExistingTestSuite()
    {
        $nodes = $this->xPath->query('/phpunit/testsuites/testsuite');

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }

        // handle situation when test suite is directly inside root node
        $nodes = $this->xPath->query('/phpunit/testsuite');

        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function addTestSuiteWithFilteredTestFiles(array $coverageTests)
    {
        $testSuites = $this->xPath->query('/phpunit/testsuites');
        $nodeToAppendTestSuite = $testSuites->item(0);

        // if there is no `testsuites` node, append to root
        if (!$nodeToAppendTestSuite) {
            $nodeToAppendTestSuite = $testSuites = $this->xPath->query('/phpunit')->item(0);
        }

        $testSuite = $this->dom->createElement('testsuite');
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
            $file = $this->dom->createElement('file', $testFilePath);

            $testSuite->appendChild($file);
        }

        $nodeToAppendTestSuite->appendChild($testSuite);
    }

    private function unique(array $coverageTests): array
    {
        $usedFileNames = [];
        $uniqueTests = [];

        foreach ($coverageTests as $coverageTest) {
            if (!in_array($coverageTest['testFilePath'], $usedFileNames, true)) {
                $uniqueTests[] = $coverageTest;
                $usedFileNames[] = $coverageTest['testFilePath'];
            }
        }

        return $uniqueTests;
    }
}
