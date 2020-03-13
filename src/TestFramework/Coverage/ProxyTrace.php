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

use Infection\TestFramework\Coverage\XmlReport\TestTrace;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * Full-pledge trace that acts as a proxy i.e. for which the tracing of the test files will be done
 * lazily.
 *
 * @internal
 * @final
 */
class ProxyTrace implements Trace
{
    /**
     * @var SplFileInfo
     */
    private $sourceFile;

    /**
     * @var TestLocations|null
     */
    private $testLocations;

    /**
     * @var iterable<TestLocations>
     */
    private $lazyTestLocations;

    /**
     * @var TestTrace|null
     */
    private $tests;

    /**
     * @param iterable<TestLocations> $lazyTestLocations
     */
    public function __construct(SplFileInfo $sourceFile, iterable $lazyTestLocations)
    {
        $this->sourceFile = $sourceFile;

        // There's no point to have it parsed right away as we may not need it, e.g. because of a filter
        $this->lazyTestLocations = $lazyTestLocations;
    }

    public function getSplFileInfo(): SplFileInfo
    {
        return $this->sourceFile;
    }

    /**
     * Used by RealPathFilterIterator
     */
    public function getRealPath(): string
    {
        $realPath = $this->sourceFile->getRealPath();

        Assert::string($realPath);

        return $realPath;
    }

    /**
     * Accessor used to update CoverageReport with TestFileTimeData.
     */
    public function retrieveTestLocations(): TestLocations
    {
        if ($this->testLocations !== null) {
            return $this->testLocations;
        }

        // TODO: maybe instead of having iterable<CoverageReport> lazyCoverageReport, we could have
        // `Closure<() => TestLocations> testLocationsFactory`: it returns only one element but
        // remains lazy
        foreach ($this->lazyTestLocations as $coverageReport) {
            // is a Generator with one yield, thus it'll only trigger here
            // (or this can be an array with one element)
            $this->testLocations = $coverageReport;

            break;
        }

        Assert::isInstanceOf($this->testLocations, TestLocations::class);
        $this->lazyTestLocations = []; // let GC have it

        return $this->testLocations;
    }

    public function getAllTestsForMutation(NodeLineRangeData $lineRange, bool $isOnFunctionSignature): iterable
    {
        return $this->getTestTrace()->getAllTestsForMutation($lineRange, $isOnFunctionSignature);
    }

    public function hasTests(): bool
    {
        return $this->getTestTrace()->hasTests();
    }

    private function getTestTrace(): TestTrace
    {
        if ($this->tests !== null) {
            return $this->tests;
        }

        $this->tests = new TestTrace($this->retrieveTestLocations());

        return $this->tests;
    }
}
