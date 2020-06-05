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

use function array_key_exists;
use Infection\FileSystem\SourceFileFilter;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use function Pipeline\take;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * Leverages a decorated trace provider in order to provide the traces but fall-backs on the
 * original source files in order to ensure all the files are included.
 *
 * @internal
 */
final class FilteredEnrichedTraceProvider implements TraceProvider
{
    private const SEEN = true;

    /**
     * @var TraceProvider
     */
    private $primaryTraceProvider;

    private $testFileDataAdder;

    private $filter;

    /**
     * @var iterable<SplFileInfo>
     */
    private $sourceFiles;

    private $onlyCovered;

    /**
     * @param iterable<SplFileInfo> $sourceFiles
     */
    public function __construct(
        TraceProvider $primaryTraceProvider,
        JUnitTestExecutionInfoAdder $testFileDataAdder,
        SourceFileFilter $filter,
        iterable $sourceFiles,
        bool $onlyCovered
    ) {
        $this->primaryTraceProvider = $primaryTraceProvider;
        $this->testFileDataAdder = $testFileDataAdder;
        $this->filter = $filter;
        $this->sourceFiles = $sourceFiles;
        $this->onlyCovered = $onlyCovered;
    }

    /**
     * @return iterable<Trace>
     */
    public function provideTraces(): iterable
    {
        /** @var iterable<Trace> $traces */
        $traces = $this->filter->filter(
            $this->primaryTraceProvider->provideTraces()
        );

        /*
         * Looking up test executing timings is not a free operation. We even had to memoize it to help speed things up.
         * Therefore we add test execution info only after applying filter to the files feed. Adding this step above the
         * filter will negatively affect performance. The greater the junit.xml report size, the more.
         */
        $enrichedTraces = $this->testFileDataAdder->addTestExecutionInfo($traces);

        if ($this->onlyCovered === true) {
            // The case where only covered files are considered
            return $enrichedTraces;
        }

        return $this->appendUncoveredFiles($enrichedTraces);
    }

    /**
     * Adds to the queue uncovered files found on disk.
     *
     * @param iterable<Trace> $traces
     *
     * @return iterable<Trace>
     */
    private function appendUncoveredFiles(iterable $traces): iterable
    {
        $filteredSourceFiles = $this->makeSourceFileMap();

        /** @var Trace $trace */
        foreach ($traces as $trace) {
            if (!array_key_exists($trace->getSourceFileInfo()->getRealPath(), $filteredSourceFiles)) {
                continue;
            }

            unset($filteredSourceFiles[$trace->getSourceFileInfo()->getRealPath()]);

            yield $trace;
        }

        foreach ($filteredSourceFiles as $splFileInfo) {
            $sourceFilePath = $splFileInfo->getRealPath();

            Assert::string($sourceFilePath);

            yield new ProxyTrace($splFileInfo, [new TestLocations()]);
        }
    }

    /**
     * Make source files whitelist.
     *
     * @return array<string, SplFileInfo>
     */
    private function makeSourceFileMap(): array
    {
        return iterator_to_array(take($this->filter->filter(
            $this->sourceFiles
        ))->map(static function (SplFileInfo $filteredSourceFile) {
            yield $filteredSourceFile->getRealPath() => $filteredSourceFile;
        }));
    }
}
