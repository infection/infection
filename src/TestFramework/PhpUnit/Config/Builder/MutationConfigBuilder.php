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

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Coverage\XmlReport\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationHelper;
use Infection\TestFramework\SafeQuery;
use function Safe\file_put_contents;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    use SafeQuery;

    private $tmpDir;
    private $projectDir;
    private $originalXmlConfigContent;
    private $xmlConfigurationHelper;
    private $jUnitTestCaseSorter;

    /**
     * @var string|null
     */
    private $originalBootstrapFile;

    /**
     * @var DOMDocument|null
     */
    private $dom;

    public function __construct(
        string $tmpDir,
        string $originalXmlConfigContent,
        XmlConfigurationHelper $xmlConfigurationHelper,
        string $projectDir,
        JUnitTestCaseSorter $jUnitTestCaseSorter
    ) {
        $this->tmpDir = $tmpDir;
        $this->projectDir = $projectDir;

        $this->originalXmlConfigContent = $originalXmlConfigContent;
        $this->xmlConfigurationHelper = $xmlConfigurationHelper;
        $this->jUnitTestCaseSorter = $jUnitTestCaseSorter;
    }

    /**
     * @param CoverageLineData[] $coverageTests
     */
    public function build(
        array $coverageTests,
        string $mutantFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath
    ): string {
        $dom = $this->getDom();
        $xPath = new DOMXPath($dom);

        $this->xmlConfigurationHelper->replaceWithAbsolutePaths($xPath);

        $originalBootstrapFile = $this->originalBootstrapFile;

        if ($originalBootstrapFile === null) {
            $originalBootstrapFile = $this->originalBootstrapFile = $this->getOriginalBootstrapFilePath($xPath);
        }

        $this->xmlConfigurationHelper->setStopOnFailure($xPath);
        $this->xmlConfigurationHelper->deactivateColours($xPath);
        $this->xmlConfigurationHelper->deactivateResultCaching($xPath);
        $this->xmlConfigurationHelper->deactivateStderrRedirection($xPath);
        $this->xmlConfigurationHelper->removeExistingLoggers($xPath);
        $this->xmlConfigurationHelper->removeExistingPrinters($xPath);
        $this->xmlConfigurationHelper->removeDefaultTestSuite($xPath);

        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmpDir,
            $mutationHash
        );

        $this->setCustomBootstrapPath($customAutoloadFilePath, $xPath);
        $this->setFilteredTestsToRun($coverageTests, $dom, $xPath);

        file_put_contents(
            $customAutoloadFilePath,
            $this->createCustomAutoloadWithInterceptor(
                $mutationOriginalFilePath,
                $mutantFilePath,
                $originalBootstrapFile
            )
        );

        $path = $this->buildPath($mutationHash);

        file_put_contents($path, $dom->saveXML());

        return $path;
    }

    private function getDom(): DOMDocument
    {
        if ($this->dom !== null) {
            return $this->dom;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->originalXmlConfigContent);

        return $this->dom = $dom;
    }

    private function createCustomAutoloadWithInterceptor(
        string $originalFilePath,
        string $mutantFilePath,
        string $originalAutoloadFile
    ): string {
        $interceptorPath = IncludeInterceptor::LOCATION;

        return sprintf(
            <<<'PHP'
<?php

%s
require_once '%s';

PHP
            ,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath),
            $originalAutoloadFile
        );
    }

    private function buildPath(string $mutationHash): string
    {
        return sprintf(
            '%s/phpunitConfiguration.%s.infection.xml',
            $this->tmpDir,
            $mutationHash
        );
    }

    private function setCustomBootstrapPath(string $customAutoloadFilePath, DOMXPath $xPath): void
    {
        $bootstrap = self::safeQuery($xPath, '/phpunit/@bootstrap');

        if ($bootstrap->length) {
            $bootstrap[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = self::safeQuery($xPath, '/phpunit')[0];
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
        $this->removeExistingTestSuiteNodes(
            self::safeQuery($xPath, '/phpunit/testsuites/testsuite')
        );

        // Handle situation when test suite is directly inside root node
        $this->removeExistingTestSuiteNodes(
            self::safeQuery($xPath, '/phpunit/testsuite')
        );
    }

    /**
     * @param DOMNodeList<DOMNode> $testSuites
     */
    private function removeExistingTestSuiteNodes(DOMNodeList $testSuites): void
    {
        foreach ($testSuites as $node) {
            $parent = $node->parentNode;

            Assert::isInstanceOf($parent, DOMNode::class);

            $parent->removeChild($node);
        }
    }

    /**
     * @param CoverageLineData[] $coverageTestCases
     */
    private function addTestSuiteWithFilteredTestFiles(
        array $coverageTestCases,
        DOMDocument $dom,
        DOMXPath $xPath
    ): void {
        $testSuites = self::safeQuery($xPath, '/phpunit/testsuites');

        $nodeToAppendTestSuite = $testSuites->item(0);

        // If there is no `testsuites` node, append to root
        if (!$nodeToAppendTestSuite) {
            $nodeToAppendTestSuite = $testSuites = self::safeQuery($xPath, '/phpunit')->item(0);
        }

        $testSuite = $dom->createElement('testsuite');
        $testSuite->setAttribute('name', 'Infection testsuite with filtered tests');

        $uniqueTestFilePaths = $this->jUnitTestCaseSorter->getUniqueSortedFileNames($coverageTestCases);

        foreach ($uniqueTestFilePaths as $testFilePath) {
            $file = $dom->createElement('file', $testFilePath);

            $testSuite->appendChild($file);
        }

        Assert::isInstanceOf($nodeToAppendTestSuite, DOMNode::class);

        $nodeToAppendTestSuite->appendChild($testSuite);
    }

    private function getOriginalBootstrapFilePath(DOMXPath $xPath): string
    {
        $bootstrap = self::safeQuery($xPath, '/phpunit/@bootstrap');

        if ($bootstrap->length) {
            return $bootstrap[0]->nodeValue;
        }

        return sprintf('%s/vendor/autoload.php', $this->projectDir);
    }
}
