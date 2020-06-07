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
     * @var array<string, SplFileInfo>
     */
    private $sourceFiles = [];

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

        foreach ($this->filter->filter(
            $sourceFiles
        ) as $filteredSourceFile) {
            /** @var SplFileInfo $filteredSourceFile */
            $sourceFilePath = $filteredSourceFile->getRealPath();

            Assert::string($sourceFilePath);

            $this->sourceFiles[$sourceFilePath] = $filteredSourceFile;
        }

        $this->onlyCovered = $onlyCovered;
    }

    /**
     * @return iterable<Trace>
     */
    public function provideTraces(): iterable
    {
        /** @var iterable<Trace> $filteredTraces */
        $filteredTraces = $this->filter->filter(
            $this->primaryTraceProvider->provideTraces()
        );

        /*
         * We need to remove traces that are not in the list of source files,
         * which could have files that were been directly specified. All the
         * while later we may need a list of files that in the list but were
         * not covered.
         */
        $intersectedTraces = take($filteredTraces)->filter(function (Trace $trace) {
            $traceRealPath = $trace->getSourceFileInfo()->getRealPath();

            Assert::string($traceRealPath);

            if (array_key_exists($traceRealPath, $this->sourceFiles)) {
                unset($this->sourceFiles[$traceRealPath]);

                return true;
            }

            return false;
        });

        /*
         * Looking up test executing timings is not a free operation. We even had to memoize it to help speed things up.
         * Therefore we add test execution info only after applying filter to the files feed. Adding this step above the
         * filter will negatively affect performance. The greater the junit.xml report size, the more.
         */
        $enrichedTraces = $this->testFileDataAdder->addTestExecutionInfo($intersectedTraces);

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
        yield from $traces;

        foreach ($this->sourceFiles as $splFileInfo) {
            yield new ProxyTrace($splFileInfo, [new TestLocations()]);
        }
    }
}
