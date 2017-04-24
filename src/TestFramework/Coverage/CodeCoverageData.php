<?php

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

class CodeCoverageData
{
    /**
     * @var \SebastianBergmann\CodeCoverage\CodeCoverage
     */
    private $coverage;

    public function __construct(string $coverageFilePath)
    {
        $this->coverage = require $coverageFilePath;
    }

    public function hasTests(string $filePath): bool
    {
        $data = $this->coverage->getData();

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
        $data = $this->coverage->getData();

        if (!isset($data[$filePath])) {
            return false;
        }

        if (!isset($data[$filePath][$line])) {
            return false;
        }

        return !empty($data[$filePath][$line]);
    }
}