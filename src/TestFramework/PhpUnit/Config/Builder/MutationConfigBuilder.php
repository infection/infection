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
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\StreamWrapper\IncludeInterceptor;
use Infection\TestFramework\Config\MutationConfigBuilder as ConfigBuilder;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseSorter;
use Infection\TestFramework\PhpUnit\Config\XmlConfigurationManipulator;
use Infection\TestFramework\SafeDOMXPath;
use function Safe\file_put_contents;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
class MutationConfigBuilder extends ConfigBuilder
{
    private ?string $originalBootstrapFile = null;

    private ?DOMDocument $dom = null;

    public function __construct(private readonly string $tmpDir, private readonly string $originalXmlConfigContent, private readonly XmlConfigurationManipulator $configManipulator, private readonly string $projectDir, private readonly JUnitTestCaseSorter $jUnitTestCaseSorter)
    {
    }

    /**
     * @param TestLocation[] $tests
     */
    public function build(
        array $tests,
        string $mutantFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $version,
    ): string {
        $dom = $this->getDom();
        $xPath = new SafeDOMXPath($dom);

        $this->configManipulator->replaceWithAbsolutePaths($xPath);

        $originalBootstrapFile = $this->originalBootstrapFile;

        if ($originalBootstrapFile === null) {
            $originalBootstrapFile = $this->originalBootstrapFile = $this->getOriginalBootstrapFilePath($xPath);
        }

        // activate PHPUnit's result cache and order tests by running defects first, then sorted by fastest first
        $this->configManipulator->handleResultCacheAndExecutionOrder($version, $xPath, $mutationHash, $this->tmpDir);
        $this->configManipulator->addFailOnAttributesIfNotSet($version, $xPath);
        $this->configManipulator->setStopOnFailureOrDefect($version, $xPath);
        $this->configManipulator->deactivateColours($xPath);
        $this->configManipulator->deactivateStderrRedirection($xPath);
        $this->configManipulator->removeExistingLoggers($xPath);
        $this->configManipulator->removeExistingPrinters($xPath);
        $this->configManipulator->removeDefaultTestSuite($xPath);

        $customAutoloadFilePath = sprintf(
            '%s/interceptor.autoload.%s.infection.php',
            $this->tmpDir,
            $mutationHash,
        );

        $this->setCustomBootstrapPath($customAutoloadFilePath, $xPath);
        $this->setFilteredTestsToRun($tests, $dom, $xPath);

        file_put_contents(
            $customAutoloadFilePath,
            $this->createCustomAutoloadWithInterceptor(
                $mutationOriginalFilePath,
                $mutantFilePath,
                $originalBootstrapFile,
            ),
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
        $success = @$dom->loadXML($this->originalXmlConfigContent);

        Assert::true($success);

        return $this->dom = $dom;
    }

    private function createCustomAutoloadWithInterceptor(
        string $originalFilePath,
        string $mutantFilePath,
        string $originalAutoloadFile,
    ): string {
        $interceptorPath = IncludeInterceptor::LOCATION;

        return sprintf(
            <<<'PHP'
                <?php

                if (function_exists('proc_nice')) {
                    proc_nice(1);
                }
                %s
                require_once '%s';

                PHP
            ,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath),
            $originalAutoloadFile,
        );
    }

    private function buildPath(string $mutationHash): string
    {
        return sprintf(
            '%s/phpunitConfiguration.%s.infection.xml',
            $this->tmpDir,
            $mutationHash,
        );
    }

    private function setCustomBootstrapPath(string $customAutoloadFilePath, SafeDOMXPath $xPath): void
    {
        $bootstrap = $xPath->query('/phpunit/@bootstrap');

        if ($bootstrap->length > 0) {
            $bootstrap[0]->nodeValue = $customAutoloadFilePath;
        } else {
            $node = $xPath->query('/phpunit')[0];
            $node->setAttribute('bootstrap', $customAutoloadFilePath);
        }
    }

    /**
     * @param TestLocation[] $tests
     */
    private function setFilteredTestsToRun(array $tests, DOMDocument $dom, SafeDOMXPath $xPath): void
    {
        $this->removeExistingTestSuite($xPath);

        $this->addTestSuiteWithFilteredTestFiles($tests, $dom, $xPath);
    }

    private function removeExistingTestSuite(SafeDOMXPath $xPath): void
    {
        $this->removeExistingTestSuiteNodes(
            $xPath->query('/phpunit/testsuites/testsuite'),
        );

        // Handle situation when test suite is directly inside root node
        $this->removeExistingTestSuiteNodes(
            $xPath->query('/phpunit/testsuite'),
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
     * @param TestLocation[] $tests
     */
    private function addTestSuiteWithFilteredTestFiles(
        array $tests,
        DOMDocument $dom,
        SafeDOMXPath $xPath,
    ): void {
        $testSuites = $xPath->query('/phpunit/testsuites');

        $nodeToAppendTestSuite = $testSuites->item(0);

        // If there is no `testsuites` node, append to root
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

    private function getOriginalBootstrapFilePath(SafeDOMXPath $xPath): string
    {
        $bootstrap = $xPath->query('/phpunit/@bootstrap');

        if ($bootstrap->length > 0) {
            return $bootstrap[0]->nodeValue;
        }

        return sprintf('%s/vendor/autoload.php', $this->projectDir);
    }
}
