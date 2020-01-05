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

namespace Infection\TestFramework\Coverage;

use function array_key_exists;
use function count;
use function dirname;
use Generator;
use Infection\TestFramework\PhpUnit\Coverage\IndexXmlCoverageParser;
use function Safe\file_get_contents;

/**
 * @internal
 */
final class XMLLineCodeCoverage implements LineCodeCoverage
{
    /**
     * @var array
     */
    private $coverage;

    private $coverageDir;
    private $parser;
    private $testFileDataProvider;
    private $testFrameworkKey;

    public function __construct(string $coverageDir, IndexXmlCoverageParser $coverageXmlParser, string $testFrameworkKey, ?TestFileDataProvider $testFileDataProvider = null)
    {
        $this->coverageDir = $coverageDir;
        $this->parser = $coverageXmlParser;
        $this->testFileDataProvider = $testFileDataProvider;
        $this->testFrameworkKey = $testFrameworkKey;
    }

    public function hasTests(string $filePath): bool
    {
        $coverageData = $this->getCoverage();

        if (!isset($coverageData[$filePath])) {
            return false;
        }

        $coveredLineTestMethods = array_filter(
            $coverageData[$filePath]->byLine,
            static function ($testMethods) {
                return count($testMethods) > 0;
            }
        );

        return count($coveredLineTestMethods) > 0;
    }

    /**
     * @return CoverageLineData[]
     */
    public function getAllTestsForMutation(
        string $filePath,
        NodeLineRangeData $lineRange,
        bool $isOnFunctionSignature
    ): array {
        if ($isOnFunctionSignature) {
            return iterator_to_array($this->getTestsForFunctionSignature($filePath, $lineRange), false);
        }

        return iterator_to_array($this->getTestsForLineRange($filePath, $lineRange), false);
    }

    /**
     * @return Generator<CoverageLineData>
     */
    private function getTestsForFunctionSignature(string $filePath, NodeLineRangeData $lineRange): Generator
    {
        foreach ($lineRange->range as $line) {
            yield from $this->getTestsForExecutedMethodOnLine($filePath, $line);
        }
    }

    /**
     * @return Generator<CoverageLineData>
     */
    private function getTestsForLineRange(string $filePath, NodeLineRangeData $lineRange): Generator
    {
        foreach ($lineRange->range as $line) {
            yield from $this->getCoverage()[$filePath]->byLine[$line] ?? [];
        }
    }

    /**
     * coverage[$sourceFilePath] = [
     *   'byMethod' => [
     *        'mutate' => ['executed' => 3, startLine => 12, endLine => 16, ...],
     *        ...
     *   ],
     *   'byLine' => [
     *       22 => [
     *           [
     *               'testMethod' => '\A\B\C::test_it_works',
     *               'testFilePath' => '/path/to/A/B/C.php',
     *               'time' => 0.34325,
     *           ],
     *           ...
     *        ]
     *    ]
     * ]
     *
     * @throws CoverageDoesNotExistException
     *
     * @return CoverageFileData[]
     */
    private function getCoverage(): array
    {
        if (!isset($this->coverage)) {
            $coverageIndexFilePath = $this->coverageDir . '/' . self::COVERAGE_INDEX_FILE_NAME;

            if (!file_exists($coverageIndexFilePath)) {
                throw CoverageDoesNotExistException::with(
                    $coverageIndexFilePath,
                    $this->testFrameworkKey,
                    dirname($coverageIndexFilePath, 2)
                );
            }

            $coverageIndexFileContent = file_get_contents($coverageIndexFilePath);

            $coverage = $this->parser->parse($coverageIndexFileContent);

            $this->addTestExecutionInfo($coverage);

            $this->coverage = $coverage;
        }

        return $this->coverage;
    }

    /**
     * @param CoverageFileData[] $coverage
     */
    private function addTestExecutionInfo(array $coverage): void
    {
        if (!$this->testFileDataProvider) {
            return;
        }

        foreach ($coverage as $sourceFilePath => $fileCoverageData) {
            foreach ($fileCoverageData->byLine as $line => $linesCoverageData) {
                foreach ($linesCoverageData as $test) {
                    $class = explode('::', $test->testMethod)[0];

                    $testFileData = $this->testFileDataProvider->getTestFileInfo($class);

                    $test->testFilePath = $testFileData->path;
                    $test->time = $testFileData->time;
                }
            }
        }
    }

    /**
     * @throws CoverageDoesNotExistException
     *
     * @return CoverageLineData[]
     */
    private function getTestsForExecutedMethodOnLine(string $filePath, int $line): array
    {
        $coverage = $this->getCoverage();

        if (!array_key_exists($filePath, $coverage)) {
            return [];
        }

        $tests = [[]];

        foreach ($coverage[$filePath]->byMethod as $method => $coverageMethodData) {
            if ($line >= $coverageMethodData->startLine && $line <= $coverageMethodData->endLine) {
                /** @var int[] $allLines */
                $allLines = range($coverageMethodData->startLine, $coverageMethodData->endLine);

                foreach ($allLines as $lineInExecutedMethod) {
                    if (array_key_exists($lineInExecutedMethod, $this->getCoverage()[$filePath]->byLine)) {
                        $tests[] = $this->getCoverage()[$filePath]->byLine[$lineInExecutedMethod];
                    }
                }

                break;
            }
        }

        return array_merge(...$tests);
    }
}
