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

namespace Infection\TestFramework\NewCoverage\PHPUnitXml;

// TODO: rather than converting directly to iterable<SourceFileInfoProvider>, this adds a layer of abstraction to expose the report as a PHP object.
//  Need to be revisted.

use Closure;
use Infection\TestFramework\NewCoverage\JUnit\JUnitReport;
use Infection\TestFramework\NewCoverage\JUnit\TestInfo;
use Infection\TestFramework\NewCoverage\PHPUnitXml\File\FileReport;
use Infection\TestFramework\NewCoverage\PHPUnitXml\File\LineCoverage;
use Infection\TestFramework\NewCoverage\PHPUnitXml\File\MethodLineRange;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\IndexReport;
use Infection\TestFramework\NewCoverage\PHPUnitXml\Index\SourceFileIndexXmlInfo;
use function array_key_exists;

final class PHPUnitXmlReport
{
    private readonly JUnitReport $jUnitReport;

    private readonly IndexReport $indexReport;

    /**
     * @var array<string, FileReport>
     */
    private array $fileReports = [];

    /**
     * @param Closure():IndexReport $getIndexReport
     * @param Closure():JUnitReport $getJUnitReport
     */
    public function __construct(
        private readonly Closure $getIndexReport,
        private readonly Closure $getJUnitReport,
    ) {
    }

    /**
     * @return iterable<SourceFileIndexXmlInfo>
     */
    public function getSourceFileInfos(): iterable
    {
        return $this->getIndexReport()->getSourceFileInfos();
    }

    /**
     * @param string $sourcePathname Canonical pathname of the source file. It
     *                               is expected to either be absolute, or it
     *                               should be relative to the PHPUnit source
     *                               (configured in the PHPUnit configuration file).
     */
    public function findSourceFileInfo(string $sourcePathname): ?SourceFileIndexXmlInfo
    {
        return $this->getIndexReport()->findSourceFileInfo($sourcePathname);
    }

    /**
     * This method is not expected to be called if the file has already been
     *  identified to not have any tests, i.e. we expect to have at least one
     *  line of executable code covered.
     *
     * @return non-empty-list<LineCoverage>
     */
    public function getLineCoverage(string $coveragePathname): array
    {
        return $this->getFileReport($coveragePathname)->getLineCoverage();
    }

    /**
     * @return list<MethodLineRange>
     */
    public function getCoveredSourceMethodLineRanges(string $coveragePathname): array
    {
        return $this->getFileReport($coveragePathname)->getCoveredSourceMethodLineRanges();
    }

    /**
     * For example, 'App\Tests\DemoTest::test_it_works#item 0'.
     */
    public function getTestInfo(string $test): TestInfo
    {
        return $this->getJUnitReport()->getTestInfo($test);
    }

    /**
     * @param string $sourcePathname Canonical pathname of the source file. It
     *                               is expected to either be absolute, or it
     *                               should be relative to the PHPUnit source
     *                               (configured in the PHPUnit configuration file).
     */
    public function hasTest(string $sourcePathname): bool
    {
        return $this->getIndexReport()->hasTest($sourcePathname);
    }

    private function getJUnitReport(): JUnitReport
    {
        if (!isset($this->jUnitReport)) {
            $this->jUnitReport = ($this->getJUnitReport)();
        }

        return $this->jUnitReport;
    }

    private function getIndexReport(): IndexReport
    {
        if (!isset($this->indexReport)) {
            $this->indexReport = ($this->getIndexReport)();
        }

        return $this->indexReport;
    }

    private function getFileReport(string $coveragePathname): FileReport
    {
        if (!array_key_exists($coveragePathname, $this->fileReports)) {
            $this->fileReports[$coveragePathname] = new FileReport($coveragePathname);
        }

        // TODO: may need to clean them up for memory efficiency
        return $this->fileReports[$coveragePathname];
    }
}
