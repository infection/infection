<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

use Infection\Mutation;
use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;

class CodeCoverageData
{
    const PHP_UNIT_COVERAGE_DIR = 'coverage-xml';
    const PHP_SPEC_COVERAGE_DIR = 'phpspec-coverage-xml';
    const COVERAGE_INDEX_FILE_NAME = 'index.xml';

    /**
     * @var array
     */
    private $coverage;

    /**
     * @var string
     */
    private $coverageDir;

    /**
     * @var CoverageXmlParser
     */
    private $parser;

    /**
     * @var TestFileDataProvider
     */
    private $testFileDataProvider;

    /**
     * @var string
     */
    private $testFrameworkKey;

    public function __construct(string $coverageDir, CoverageXmlParser $coverageXmlParser, string $testFrameworkKey, TestFileDataProvider $testFileDataProvider = null)
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
            $coverageData[$filePath]['byLine'],
            function ($testMethods) {
                return count($testMethods) > 0;
            }
        );

        return count($coveredLineTestMethods) > 0;
    }

    public function hasTestsOnLine(string $filePath, int $line): bool
    {
        $coverageData = $this->getCoverage();

        if (!isset($coverageData[$filePath])) {
            return false;
        }

        if (!isset($coverageData[$filePath]['byLine'][$line])) {
            return false;
        }

        return !empty($coverageData[$filePath]['byLine'][$line]);
    }

    public function hasExecutedMethodOnLine(string $filePath, int $line): bool
    {
        $coverage = $this->getCoverage();

        if (!array_key_exists($filePath, $coverage)) {
            return false;
        }

        foreach ($coverage[$filePath]['byMethod'] as $method => $coverageInfo) {
            if ($coverageInfo['executed'] === 0) {
                continue;
            }

            if ($line >= $coverageInfo['startLine'] && $line <= $coverageInfo['endLine']) {
                return true;
            }
        }

        return false;
    }

    public function getAllTestsFor(Mutation $mutation): array
    {
        $filePath = $mutation->getOriginalFilePath();
        $line = $mutation->getAttributes()['startLine'];

        if ($mutation->isOnFunctionSignature()) {
            if ($this->hasExecutedMethodOnLine($filePath, $line)) {
                return $this->getTestsForExecutedMethodOnLine($filePath, $line);
            }

            return [];
        }

        if ($this->hasTestsOnLine($filePath, $line)) {
            return $this->getCoverage()[$filePath]['byLine'][$line];
        }

        return [];
    }

    /**
     * coverage[$sourceFilePath] = [
     *   'byMethod' => [
     *        'mutate' => ['executed' => 3, startLine => 12, endLine => 16, ...],
     *        ...
     *   ],
     *   'byLine' => [
     *       22 => [
     *          'testMethod' => '\A\B\C::test_it_works',
     *          'testFilePath' => '/path/to/A/B/C.php',
     *          'time' => 0.34325,
     *       ]
     *    ]
     * ]
     *
     * @throws CoverageDoesNotExistException
     */
    private function getCoverage(): array
    {
        if (null === $this->coverage) {
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

            $coverage = $this->addTestExecutionInfo($coverage);

            $this->coverage = $coverage;
        }

        return $this->coverage;
    }

    private function addTestExecutionInfo(array $coverage): array
    {
        if ($this->testFileDataProvider === null) {
            return $coverage;
        }

        $newCoverage = $coverage;

        foreach ($newCoverage as $sourceFilePath => &$fileCoverageData) {
            foreach ($fileCoverageData['byLine'] as $line => &$lineCoverageData) {
                foreach ($lineCoverageData as &$test) {
                    $class = explode('::', $test['testMethod'])[0];

                    $testFileData = $this->testFileDataProvider->getTestFileInfo($class);

                    $test['testFilePath'] = $testFileData['path'];
                    $test['time'] = $testFileData['time'];
                }
                unset($test);
            }
            unset($lineCoverageData);
        }
        unset($fileCoverageData);

        return $newCoverage;
    }

    private function getTestsForExecutedMethodOnLine(string $filePath, int $line): array
    {
        $coverage = $this->getCoverage();

        $tests = [[]];

        foreach ($coverage[$filePath]['byMethod'] as $method => $coverageInfo) {
            if ($line >= $coverageInfo['startLine'] && $line <= $coverageInfo['endLine']) {
                $allLines = range($coverageInfo['startLine'], $coverageInfo['endLine']);

                foreach ($allLines as $lineInExecutedMethod) {
                    if (array_key_exists($lineInExecutedMethod, $this->getCoverage()[$filePath]['byLine'])) {
                        $tests[] = $this->getCoverage()[$filePath]['byLine'][$lineInExecutedMethod];
                    }
                }

                break;
            }
        }

        return array_merge(...$tests);
    }
}
