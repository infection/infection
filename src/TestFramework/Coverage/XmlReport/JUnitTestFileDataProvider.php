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

namespace Infection\TestFramework\Coverage\XmlReport;

use DOMDocument;
use Infection\TestFramework\SafeDOMXPath;
use function Safe\preg_replace;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class JUnitTestFileDataProvider implements TestFileDataProvider
{
    private $jUnitFilePath;

    /**
     * @var SafeDOMXPath|null
     */
    private $xPath;

    public function __construct(string $jUnitFilePath)
    {
        $this->jUnitFilePath = $jUnitFilePath;
    }

    /**
     * @throws TestFileNameNotFoundException
     */
    public function getTestFileInfo(string $fullyQualifiedClassName): TestFileTimeData
    {
        $xPath = $this->getXPath();

        $nodes = $xPath->query(
            sprintf('//testsuite[@name="%s"]', $fullyQualifiedClassName)
        );

        if ($nodes->length === 0) {
            // Try another format where the class name is inside `class` attribute of `testcase` tag
            $nodes = $xPath->query(
                sprintf('//testcase[@class="%s"]', $fullyQualifiedClassName)
            );
        }

        if ($nodes->length === 0) {
            $feature = preg_replace('/^(.*):+.*$/', '$1.feature', $fullyQualifiedClassName);
            // try another format where the class name is inside `file` attribute of `testcase` tag
            $nodes = $xPath->query(
                sprintf('//testcase[contains(@file, "%s")]', $feature)
            );
        }

        if ($nodes->length === 0) {
            throw TestFileNameNotFoundException::notFoundFromFQN(
                $fullyQualifiedClassName,
                $this->jUnitFilePath
            );
        }

        return new TestFileTimeData(
            $nodes[0]->getAttribute('file'),
            (float) $nodes[0]->getAttribute('time')
        );
    }

    private function getXPath(): SafeDOMXPath
    {
        if (!$this->xPath) {
            $this->xPath = self::createXPath($this->jUnitFilePath);
        }

        return $this->xPath;
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
