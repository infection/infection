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

use Infection\TestFramework\Coverage\XmlReport\FileCodeCoverage;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * Workhorse AKA envelope for all things coverage-related in regard of mutators.
 *
 * @internal
 * @final
 */
class SourceFileData implements LineCodeCoverage
{
    /**
     * @var SplFileInfo
     */
    private $sourceFile;

    /**
     * @var CoverageReport|null
     */
    private $coverageReport;

    /**
     * @var iterable<CoverageReport>
     */
    private $lazyCoverageReport;

    /**
     * @var FileCodeCoverage|null
     */
    private $lineCodeCoverage;

    /**
     * @param iterable<CoverageReport> $lazyCoverageReport
     */
    public function __construct(SplFileInfo $sourceFile, iterable $lazyCoverageReport)
    {
        $this->sourceFile = $sourceFile;

        // There's no point to have it parsed right away as we may not need it, e.g. because of a filter
        $this->lazyCoverageReport = $lazyCoverageReport;
    }

    public function getSplFileInfo(): SplFileInfo
    {
        return $this->sourceFile;
    }

    /**
     * Accessor used to update CoverageReport with TestFileTimeData.
     */
    public function retrieveCoverageReport(): CoverageReport
    {
        if ($this->coverageReport !== null) {
            return $this->coverageReport;
        }

        foreach ($this->lazyCoverageReport as $coverageReport) {
            // is a Generator with one yield, thus it'll only trigger here
            // (or this can be an array with one element)
            $this->coverageReport = $coverageReport;

            break;
        }

        Assert::isInstanceOf($this->coverageReport, CoverageReport::class);
        $this->lazyCoverageReport = []; // let GC have it

        return $this->coverageReport;
    }

    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature): iterable
    {
        return $this->getFileCodeCoverage()->getAllTestsForMutation($lineRange, $isOnFunctionSignature);
    }

    public function hasTests(): bool
    {
        return $this->getFileCodeCoverage()->hasTests();
    }

    private function getFileCodeCoverage(): FileCodeCoverage
    {
        if ($this->lineCodeCoverage !== null) {
            return $this->lineCodeCoverage;
        }

        $this->lineCodeCoverage = new FileCodeCoverage($this->retrieveCoverageReport());

        return $this->lineCodeCoverage;
    }
}
