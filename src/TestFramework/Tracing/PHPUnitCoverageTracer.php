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

use function explode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\TestFramework\Coverage\TestLocations;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlProvider;
use Infection\TestFramework\NewCoverage\PHPUnitXml\PHPUnitXmlReport;
use Symfony\Component\Finder\SplFileInfo;

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
            return new EmptyTrace($fileInfo);
        }

        return new LazyTrace(
            $fileInfo,
            fn () => $this->createTestLocations($reportFileInfo),
        );
    }

    private function createTestLocations(SourceFileIndexXmlInfo $fileInfo): TestLocations
    {
        $coverage = $this->getReport()->getCoverage($fileInfo->coveragePathname);

        $lines = [];

        foreach ($coverage as $item) {
            foreach ($item->coveredBy as $test) {
                $testCaseClassName = explode('::', $test, 2)[0];
                $testInfo = $this->getReport()->getTestInfo($testCaseClassName);

                $lines[$item->lineNumber][] = new TestLocation(
                    $test,
                    $testInfo->location,
                    $testInfo->executionTime,
                );
            }
        }

        return new TestLocations(
            $lines,
            [],
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
