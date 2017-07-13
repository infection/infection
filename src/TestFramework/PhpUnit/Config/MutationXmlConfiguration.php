<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);


namespace Infection\TestFramework\PhpUnit\Config;

use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\TestFramework\PhpUnit\Config\Path\PathReplacer;

class MutationXmlConfiguration extends AbstractXmlConfiguration
{
    /**
     * @var string
     */
    private $customAutoloadFilePath;

    /**
     * @var array
     */
    private $coverageTests;

    public function __construct(string $tempDirectory, string $originalXmlConfigPath, PathReplacer $pathReplacer, string $customAutoloadFilePath, array $coverageTests)
    {
        parent::__construct($tempDirectory, $originalXmlConfigPath, $pathReplacer);

        $this->customAutoloadFilePath = $customAutoloadFilePath;
        $this->coverageTests = $coverageTests;
    }

    public function getXml() : string
    {
        $originalXml = file_get_contents($this->originalXmlConfigPath);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($originalXml);

        $xPath = new \DOMXPath($dom);

        $this->replaceWithAbsolutePaths($xPath);
        $this->setCustomAutoLoaderPath($xPath);
        $this->setStopOnFailure($xPath);
        $this->deactivateColours($xPath);
        $this->removeExistingLoggers($dom, $xPath);
        $this->setFilteredTestsToRun($dom, $xPath);

        return $dom->saveXML();
    }

    private function setCustomAutoLoaderPath(\DOMXPath $xPath)
    {
        $node = $xPath->query('/phpunit/@bootstrap')[0];

        $node->nodeValue = $this->customAutoloadFilePath;
    }

    private function setFilteredTestsToRun(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $this->removeExistingTestSuite($xPath);

        $this->addTestSuiteWIthFilteredTestFiles($dom, $xPath);
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

    private function addTestSuiteWIthFilteredTestFiles(\DOMDocument $dom, \DOMXPath $xPath)
    {
        $testSuites = $xPath->query('/phpunit/testsuites');
        $nodeToAppendTestSuite = $testSuites->item(0);

        // if there is no `testsuites` node, append to root
        if (!$nodeToAppendTestSuite) {
            $nodeToAppendTestSuite = $testSuites = $xPath->query('/phpunit')->item(0);
        }

        $testSuite = $dom->createElement('testsuite');
        $testSuite->setAttribute('name', 'Infection testsuite with filtered tests');

        $uniqueCoverageTests = $this->unique($this->coverageTests);

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