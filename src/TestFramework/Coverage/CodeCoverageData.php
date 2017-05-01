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

    /**
     * @var string
     */
    private $coverageDir;

    /**
     * @var CoverageXmlParser
     */
    private $parser;

    public function __construct(string $coverageDir, CoverageXmlParser $coverageXmlParser)
    {
        $this->coverageDir = $coverageDir;
        $this->parser = $coverageXmlParser;
    }

    public function hasTests(string $filePath): bool
    {
        $coverageData = $this->getCoverage();

        if (!isset($coverageData[$filePath])) {
            return false;
        }

        $coveredLineTestMethods = array_filter(
            $coverageData[$filePath],
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

        if (!isset($coverageData[$filePath][$line])) {
            return false;
        }

        return !empty($coverageData[$filePath][$line]);
    }

    private function getCoverage(): array
    {
        if (null === $this->coverage) {
            $coverageIndexFilePath = $this->coverageDir . '/' . self::COVERAGE_INDEX_FILE_NAME;
            $coverageIndexFileContent = file_get_contents($coverageIndexFilePath);

            $this->coverage = $this->parser->parse($coverageIndexFileContent);
        }

        return $this->coverage;
    }
}