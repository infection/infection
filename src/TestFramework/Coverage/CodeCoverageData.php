<?php

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

use Infection\TestFramework\PhpUnit\Coverage\CoverageXmlParser;

class CodeCoverageData
{
    const COVERAGE_DIR = 'coverage-xml';
    const COVERAGE_INDEX_FILE_NAME = 'index.xml';

    /**
     * @var array
     */
    private $coverage;

    public function __construct(string $coverageDir, CoverageXmlParser $coverageXmlParser)
    {
        $coverageIndexFilePath = $coverageDir . '/' . self::COVERAGE_INDEX_FILE_NAME;
        $coverageIndexFileContent = file_get_contents($coverageIndexFilePath);

        $this->coverage = $coverageXmlParser->parse($coverageIndexFileContent);
    }

    public function hasTests(string $filePath): bool
    {
        $data = $this->coverage;

        if (!isset($data[$filePath])) {
            return false;
        }

        $coveredLineTestMethods = array_filter(
            $data[$filePath],
            function ($testMethods) {
                return count($testMethods) > 0;
            }
        );

        return count($coveredLineTestMethods) > 0;
    }

    public function hasTestsOnLine(string $filePath, int $line): bool
    {
        $data = $this->coverage;

        if (!isset($data[$filePath])) {
            return false;
        }

        if (!isset($data[$filePath][$line])) {
            return false;
        }

        return !empty($data[$filePath][$line]);
    }
}