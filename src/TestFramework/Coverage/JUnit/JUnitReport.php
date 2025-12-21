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

use DOMElement;
use DOMNodeList;
use Infection\TestFramework\Coverage\Exception\TestNotFound;
use Infection\TestFramework\XML\SafeDOMXPath;
use Webmozart\Assert\Assert;
use function array_key_exists;
use function preg_replace;
use function sprintf;

/**
 * @phpstan-type TestInfo = array{location: string, executionTime: float}
 */
final class JUnitReport
{
    private SafeDOMXPath $xPath;

    /**
     * @var array<string, TestInfo>
     */
    private array $indexedTestInfos = [];

    private bool $traversed = false;

    public function __construct(
        private readonly string $pathname,
    ) {
    }

    /**
     * Returns information of the test for a given test string.
     *
     * The form of the test string depends of the JUnit format of the test
     * framework used. For example:
     *
     * - PHPUnit JUnit:
     *      - 'App\Tests\DemoTest::test_it_works#item 0': a PHPUnit test ID.
     *      - 'App\Tests\DemoTest::test_it_works': a PHPUnit test suite (i.e. contains multiple test IDs).
     *      - 'App\Tests\DemoTest': another PHPUnit test suite.
     * - Codeception BDD: 'FeatureA:Scenario A1'
     *
     * The output is the location of the test and time of execution. The location
     * path may change based on the format. For example:
     *
     * - PHPUnit: an absolute path to the test file.
     * - Codeception BDD: the path is relative to the project root.
     *
     * TODO: complete the docs here; the docs of the CoverageReport (calling class).
     *
     * @throws TestNotFound
     *
     * @return TestInfo
     */
    public function getTestInfo(string $testId): array
    {
        return array_key_exists($testId, $this->indexedTestInfos)
            ? $this->indexedTestInfos[$testId]
            : $this->lookup($testId);
    }

    /**
     * @throws TestNotFound
     *
     * @return TestInfo
     */
    private function lookup(string $testId): array
    {
        $nodes = $this->findNode($testId);

        if ($nodes->length === 0) {
            throw TestNotFound::forTestId(
                $testId,
                $this->pathname,
            );
        }

        Assert::same($nodes->length, 1);
        $node = $nodes->item(0);
        Assert::isInstanceOf($node, DOMElement::class);

        $testInfo = [
            'location' => $node->getAttribute('file'),
            'executionTime' => (float) $node->getAttribute('time'),
        ];

        $this->indexedTestInfos[$testId] = $testInfo;

        return $testInfo;
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
        // A default format for <testsuite>
        yield sprintf(
            '//testsuite[@name="%s"][1]',
            $testCaseClassName,
        );

        // A format where the class name is inside `class` attribute of `testcase` tag
        yield sprintf(
            '//testcase[@class="%s"][1]',
            $testCaseClassName,
        );

        // A format where the class name is inside `file` attribute of `testcase` tag
        yield sprintf(
            '//testcase[contains(@file, "%s")][1]',
            // Example:
            // App\Controller\AdminCest:FeatureA
            // => App\Controller\AdminCest.feature
            preg_replace('/^(.*):+.*$/', '$1.feature', $testCaseClassName),
        );

        // A format where the class name parsed from feature and is inside `class` attribute of `testcase` tag
        yield sprintf(
            '//testcase[@class="%s"][1]',
            // Example:
            // App\Controller\AdminCest:FeatureA
            // => App\Controller\AdminCest
            preg_replace('/^(.*):+.*$/', '$1', $testCaseClassName),
        );
    }

    private function getXPath(): SafeDOMXPath
    {
        return $this->xPath ??= $this->createXPath();
    }

    private function createXPath(): SafeDOMXPath
    {
        $this->assertFileWasNotTraversed();

        return SafeDOMXPath::fromFile($this->pathname);
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
