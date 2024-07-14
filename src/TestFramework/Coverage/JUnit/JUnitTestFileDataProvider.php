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

namespace Infection\TestFramework\Coverage\JUnit;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use Infection\TestFramework\SafeDOMXPath;
use function Safe\preg_replace;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class JUnitTestFileDataProvider implements TestFileDataProvider
{
    private ?SafeDOMXPath $xPath = null;

    public function __construct(private readonly JUnitReportLocator $jUnitLocator)
    {
    }

    /**
     * @throws TestFileNameNotFoundException
     */
    public function getTestFileInfo(string $fullyQualifiedClassName): TestFileTimeData
    {
        $xPath = $this->getXPath();

        /** @var DOMNodeList<DOMElement>|null $nodes */
        $nodes = null;

        foreach (self::testCaseMapGenerator($fullyQualifiedClassName) as $queryString => $placeholder) {
            $nodes = $xPath->query(sprintf($queryString, $placeholder));

            if ($nodes->length !== 0) {
                break;
            }
        }

        Assert::notNull($nodes);

        if ($nodes->length === 0) {
            throw TestFileNameNotFoundException::notFoundFromFQN(
                $fullyQualifiedClassName,
                $this->jUnitLocator->locate(),
            );
        }

        Assert::same($nodes->length, 1);

        return new TestFileTimeData(
            $nodes[0]->getAttribute('file'),
            (float) $nodes[0]->getAttribute('time'),
        );
    }

    /**
     * @return iterable<string, string>
     */
    private static function testCaseMapGenerator(string $fullyQualifiedClassName): iterable
    {
        // A default format for <testsuite>
        yield '//testsuite[@name="%s"][1]' => $fullyQualifiedClassName;

        // A format where the class name is inside `class` attribute of `testcase` tag
        yield '//testcase[@class="%s"][1]' => $fullyQualifiedClassName;

        // A format where the class name is inside `file` attribute of `testcase` tag
        yield '//testcase[contains(@file, "%s")][1]' => preg_replace('/^(.*):+.*$/', '$1.feature', $fullyQualifiedClassName);

        // A format where the class name parsed from feature and is inside `class` attribute of `testcase` tag
        yield '//testcase[@class="%s"][1]' => preg_replace('/^(.*):+.*$/', '$1', $fullyQualifiedClassName);
    }

    private function getXPath(): SafeDOMXPath
    {
        return $this->xPath ??= self::createXPath($this->jUnitLocator->locate());
    }

    private static function createXPath(string $jUnitPath): SafeDOMXPath
    {
        Assert::fileExists($jUnitPath);

        $dom = new DOMDocument();
        $success = @$dom->load($jUnitPath);

        Assert::true($success);

        return new SafeDOMXPath($dom);
    }
}
