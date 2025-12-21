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

namespace Infection\TestFramework\PhpUnit;

use function array_map;
use DomainException;
use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\JUnit\JUnitReport;
use Infection\TestFramework\Coverage\PHPUnitXml\File\FileReport;
use Infection\TestFramework\Coverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use Infection\TestFramework\Coverage\PHPUnitXml\PHPUnitXmlReport;
use Infection\TestFramework\Coverage\PHPUnitXml\PHPUnitXmlReportFactory;
use Infection\TestFramework\Coverage\Throwable\TestNotFound;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\TestFramework\Tracing\Trace\LazyTrace;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 *
 * @phpstan-import-type TestInfo from JUnitReport
 * @phpstan-import-type LineCoverage from FileReport
 * @phpstan-import-type MethodLineRange from FileReport
 */
final class PHPUnitCoverageTracer implements Tracer
{
    private PHPUnitXmlReport $report;

    public function __construct(
        private readonly PHPUnitXmlReportFactory $parser,
    ) {
    }

    public function hasTrace(SplFileInfo $fileInfo): bool
    {
        throw new DomainException('Not implemented.');
    }

    public function trace(SplFileInfo $fileInfo): Trace
    {
        $report = $this->getReport();

        $reportFileInfo = $report->findSourceFileInfo($fileInfo->getPathname());

        if ($reportFileInfo === null) {
            return new EmptyTrace($fileInfo);
        }

        return new LazyTrace(
            $fileInfo,
            fn () => $this->createTestLocations($reportFileInfo),
        );
    }

    /**
     * @throws TestNotFound
     */
    private function createTestLocations(SourceFileIndexXmlInfo $fileInfo): TestLocations
    {
        return new TestLocations(
            $this->createTestLocationsByLine($fileInfo),
            $this->getReport()->getIndexCoveredSourceMethodLineRanges(
                $fileInfo->coveragePathname,
            ),
        );
    }

    /**
     * @throws TestNotFound
     * @return TestLocation
     */
    private function createTestLocationsByLine(SourceFileIndexXmlInfo $fileInfo): array
    {
        $linesCoverage = $this->getReport()->getLineCoverage($fileInfo->coveragePathname);

        $lines = [];

        foreach ($linesCoverage as $lineCoverage) {
            /** @var LineCoverage $lineCoverage */
            $lines[$lineCoverage['lineNumber']] = array_map(
                $this->createTestLocation(...),
                $lineCoverage['coveredBy'],
            );
        }

        return $lines;
    }

    /**
     * @throws TestNotFound
     */
    private function createTestLocation(string $test): TestLocation
    {
        $testCaseClassName = explode('::', $test, 2)[0];
        $testInfo = $this->getReport()->getTestInfo($testCaseClassName);

        return new TestLocation(
            $test,
            $testInfo['location'],
            $testInfo['executionTime'],
        );
    }

    private function getReport(): PHPUnitXmlReport
    {
        if (!isset($this->report)) {
            $this->report = $this->parser->get();
        }

        return $this->report;
    }
}
