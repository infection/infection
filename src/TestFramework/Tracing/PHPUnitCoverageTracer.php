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

namespace Infection\TestFramework\Tracing;

use function array_map;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\NewCoverage\PHPUnitXml\File\LineCoverage;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlProvider;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlReport;
use SplFileInfo;

/**
 * @internal
 */
final class PHPUnitCoverageTracer
{
    private PHPUnitXmlReport $report;

    public function __construct(
        private readonly PHPUnitXmlProvider $parser,
    ) {
    }

    public function trace(SplFileInfo $fileInfo): Trace
    {
        $report = $this->getReport();

        $reportFileInfo = $report->findSourceFileInfo($fileInfo->getPathname());

        if ($reportFileInfo === null) {
            return new EmptyTrace();
        }

        $testLocations = $this->createTestLocations($reportFileInfo);

        return new LazyTrace(
            // TODO: SplFileInfo compatibility issue
            new SplFileInfo($fileInfo->sourcePathname),
            $testLocations,
        );
    }

    /**
     * @return list<TestLocation>
     */
    private function createTestLocations(SourceFileIndexXmlInfo $fileInfo): array
    {
        $coverage = $this->getReport()->getCoverage($fileInfo->coveragePathname);

        return array_map(
            fn (LineCoverage $coverage) => $this->createTestLocation(
                $lineCoverage,
                $fileInfo->coveragePathname,
            ),
            $coverage,
        );
    }

    private function createTestLocation(LineCoverage $coverage): TestLocation
    {
        // TODO: maybe there is more to it here... We get the path from here
        // but it is a bit unclear why/what.
        // The report gives the exact coveredBy -> we should get the timing for that
        $executionTime = $this->getReport()->getTestSuiteExecutionTime(
            $coverage->testCaseClassName,
        );

        return new TestLocation(
            $coverage->getMethod(), // TODO: review naming
            $executionTime->path,
            $executionTime->time,
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
