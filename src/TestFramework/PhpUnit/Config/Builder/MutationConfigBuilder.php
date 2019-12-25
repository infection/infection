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

namespace Infection\TestFramework\PhpUnit\Config\Builder;

use function assert;
use function dirname;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;

/**
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    private $tempDirectory;
    private $projectDir;
    private $xmlConfigurationHelper;
    private $dom;
    private $jUnitTestCaseSorter;

    public function __construct(
        string $tempDirectory,
        string $originalXmlConfigContent,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $projectDir,
        JUnitTestCaseSorter $jUnitTestCaseSorter
    ) {
        $this->tempDirectory = $tempDirectory;
        $this->projectDir = $projectDir;

        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->jUnitTestCaseSorter = $jUnitTestCaseSorter;

        $this->dom = new DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        $this->dom->loadXML($originalXmlConfigContent);
    }

    /**
     * @param CoverageLineData[] $coverageTests
     */
    public function build(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath
    ): string {
        // clone the dom document because it's mutated later
        $dom = clone $this->dom;

        $xPath = new DOMXPath($dom);

        $this->xmlConfigurationHelper->replaceWithAbsolutePaths($xPath);
        $this->xmlConfigurationHelper->setStopOnFailure($xPath);
        $this->xmlConfigurationHelper->deactivateColours($xPath);
        $this->xmlConfigurationHelper->deactivateResultCaching($xPath);
        $this->xmlConfigurationHelper->deactivateStderrRedirection($xPath);
        $this->xmlConfigurationHelper->removeExistingLoggers($xPath);
        $this->xmlConfigurationHelper->removeExistingPrinters($xPath);
        $this->xmlConfigurationHelper->removeDefaultTestSuite($xPath);

        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tempDirectory,
            $mutationHash
        );

        $originalAutoloadFile = $this->getOriginalBootstrapFilePath($xPath);

        $this->setCustomBootstrapPath($customAutoloadFilePath, $xPath);
        $this->setFilteredTestsToRun($coverageTests, $dom, $xPath);

        file_put_contents($customAutoloadFilePath, $this->createCustomAutoloadWithInterceptor($mutationOriginalFilePath, $mutatedFilePath, $originalAutoloadFile));

        $path = $this->buildPath($mutationHash);

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function createCustomAutoloadWithInterceptor(string $originalFilePath, string $mutatedFilePath, string $originalAutoloadFile): string
    {
        $interceptorPath = dirname(__DIR__, 4) . '/StreamWrapper/IncludeInterceptor.php';

        $customAutoload = <<<AUTOLOAD
<?php

%s
require_once '{$originalAutoloadFile}';

AUTOLOAD;

        return sprintf($customAutoload, $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutatedFilePath));
    }

    private function buildPath(string $mutationHash): string
    {
        $fileName = sprintf('phpunitConfiguration.%s.infection.xml', $mutationHash);

        return $this->tempDirectory . '/' . $fileName;
    }

    private function setCustomBootstrapPath(string $customAutoloadFilePath, DOMXPath $xPath): void
    {
        $nodeList = $xPath->query('/phpunit/@bootstrap');

        if ($nodeList->length) {
            $nodeList[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('bootstrap', $customAutoloadFilePath);
        }
    }

    /**
     * @param CoverageLineData[] $coverageTests
     */
    private function setFilteredTestsToRun(array $coverageTests, DOMDocument $dom, DOMXPath $xPath): void
    {
        $this->removeExistingTestSuite($xPath);

        $this->addTestSuiteWithFilteredTestFiles($coverageTests, $dom, $xPath);
    }

    private function removeExistingTestSuite(DOMXPath $xPath): void
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

    /**
     * @param CoverageLineData[] $coverageTestCases
     */
    private function addTestSuiteWithFilteredTestFiles(array $coverageTestCases, DOMDocument $dom, DOMXPath $xPath): void
    {
        $testSuites = $xPath->query('/phpunit/testsuites');
        $nodeToAppendTestSuite = $testSuites->item(0);

        // if there is no `testsuites` node, append to root
        if (!$nodeToAppendTestSuite) {
            $nodeToAppendTestSuite = $testSuites = $xPath->query('/phpunit')->item(0);
        }

        $testSuite = $dom->createElement('testsuite');
        $testSuite->setAttribute('name', 'Infection testsuite with filtered tests');

        $uniqueTestFilePaths = $this->jUnitTestCaseSorter->getUniqueSortedFileNames($coverageTestCases);

        foreach ($uniqueTestFilePaths as $testFilePath) {
            $file = $dom->createElement('file', $testFilePath);

            $testSuite->appendChild($file);
        }

        assert($nodeToAppendTestSuite instanceof DOMNode);

        $nodeToAppendTestSuite->appendChild($testSuite);
    }

    private function getOriginalBootstrapFilePath(DOMXPath $xPath): string
    {
        $nodeList = $xPath->query('/phpunit/@bootstrap');

        if ($nodeList->length) {
            return $nodeList[0]->nodeValue;
        }

        return sprintf('%s/vendor/autoload.php', $this->projectDir);
    }
}
