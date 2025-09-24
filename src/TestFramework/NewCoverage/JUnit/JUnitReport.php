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

namespace Infection\TestFramework\NewCoverage\JUnit;

// TODO: rather than converting directly to TestFileTimeData, this adds a layer of abstraction to expose the report as a PHP object.
//  Need to be revisted.
use function array_key_exists;
use function dirname;
use DOMElement;
use DOMNodeList;
use Generator;
use Infection\TestFramework\Coverage\JUnit\TestFileNameNotFoundException;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use Infection\TestFramework\XML\SafeDOMXPath;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Webmozart\Assert\Assert;

final class JUnitReport
{
    private readonly string $coverageDirPathname;

    private SafeDOMXPath $xPath;

    /**
     * @var array<string, float>
     */
    private array $indexedExecutionTimes = [];

    private Generator $fileInfosGenerator;

    private bool $traversed = false;

    private string $source;

    public function __construct(
        private readonly string $pathname,
    ) {
        $this->coverageDirPathname = dirname($pathname);
    }

    /**
     * For example, 'App\Tests\DemoTest::test_it_works#item 0'.
     */
    public function getTestInfo(string $test): TestInfo
    {
        return array_key_exists($test, $this->indexedExecutionTimes)
            ? $this->indexedExecutionTimes[$test]
            : $this->lookup($test);
    }

    private function lookup(string $testCaseClassName): float
    {
        $nodes = $this->findNode($testCaseClassName);

        if ($nodes->length === 0) {
            throw TestFileNameNotFoundException::notFoundFromFQN(
                $testCaseClassName,
                $this->pathname,
            );
        }

        Assert::same($nodes->length, 1);
        $node = $nodes->item(0);
        Assert::isInstanceOf($node, DOMElement::class);

        $executionTime = (float) $node->getAttribute('time');

        $this->indexedExecutionTimes[$testCaseClassName] = $executionTime;

        return $executionTime;
    }

    private function findNode(string $testCaseClassName): DOMNodeList
    {
        $nodes = null;

        foreach (self::xPathQueries($testCaseClassName) as $query) {
            $nodes = $this->getXPath()->queryList($query);

            if ($nodes->length > 0) {
                break;
            }
        }

        Assert::notNull($nodes);

        return $nodes;
    }

    /**
     * @return iterable<string, string>
     */
    private static function xPathQueries(string $testCaseClassName): iterable
    {
        yield sprintf(
            '//testsuite[@name="%s"][1]',
            $testCaseClassName,
        );

        //        // A default format for <testsuite>
        //        yield '//testsuite[@name="%s"][1]' => $testCaseClassName;
        //
        //        // A format where the class name is inside `class` attribute of `testcase` tag
        //        yield '//testcase[@class="%s"][1]' => $testCaseClassName;
        //
        //        // A format where the class name is inside `file` attribute of `testcase` tag
        //        yield '//testcase[contains(@file, "%s")][1]' => preg_replace('/^(.*):+.*$/', '$1.feature', $testCaseClassName);
        //
        //        // A format where the class name parsed from feature and is inside `class` attribute of `testcase` tag
        //        yield '//testcase[@class="%s"][1]' => preg_replace('/^(.*):+.*$/', '$1', $testCaseClassName);
    }

    /**
     * @return Generator<SourceFileIndexXmlInfo>
     */
    private function getFileInfosGenerator(): Generator
    {
        $source = $this->getPhpunitSource();
        $files = $this->getXPath()->queryList('//coverage:file');

        foreach ($files as $file) {
            Assert::isInstanceOf($file, DOMElement::class);

            yield SourceFileIndexXmlInfo::fromNode(
                $file,
                $this->coverageDirPathname,
                $source,
            );
        }

        $this->traversed = true;
        unset($this->xPath);
        unset($this->fileInfosGenerator);
    }

    private function getPhpunitSource(): string
    {
        if (!isset($this->source)) {
            $project = $this->getXPath()->queryElement('/coverage:phpunit/coverage:project');
            $this->source = $project->getAttribute('source');
        }

        return $this->source;
    }

    private function getXPath(): SafeDOMXPath
    {
        return $this->xPath ??= $this->createXPath();
    }

    private function createXPath(): SafeDOMXPath
    {
        $this->assertFileWasNotTraversed();

        $xPath = SafeDOMXPath::fromFile($this->pathname);

        // The default PHPUnit namespace is "https://schema.phpunit.de/coverage/1.0".
        // It is quite verbose and would be annoying to use it everywhere.
        // Instead, it is better to introduce an easy to write and read namespace
        // that we can use in the queries.
        // TODO: to check if there is any requiring a namespace
        //        $xPath->registerNamespace(
        //            'coverage',
        //            $xPath->document->documentElement->namespaceURI,
        //        );

        return $xPath;
    }

    private function assertFileWasNotTraversed(): void
    {
        Assert::false(
            $this->traversed,
            sprintf(
                'Did not expect to create an XPath for the file "%s": The file was already traversed.',
                $this->pathname,
            ),
        );
    }
}
