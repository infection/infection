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
use Infection\Mutation\MutationGenerator;
use Infection\TestFramework\Coverage\JUnit\JUnitTestExecutionInfoAdder;
use Infection\TestFramework\Coverage\XmlReport\PhpUnitXmlCoveredFileDataProvider;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * Assembles a ready feed of SourceFileData from different sources. Feeds data into MutationGenerator.
 * Does not known about differences between adapters and what not.
 *
 * @internal
 *
 * TODO: FilteredSourceFilesTraceProvider: Leverages a decorated trace provider in order to provide
 *          the traces but fall-backs on the original source files in order to ensure all the
 *          files are included.
 */
final class SourceFileDataFactory implements SourceFileDataProvider
{
    private const SEEN = true;

    /**
     * @var SourceFileDataProvider|PhpUnitXmlCoveredFileDataProvider
     */
    private $primaryCoverageProvider;

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
        SourceFileDataProvider $primaryCoverageProvider,
        JUnitTestExecutionInfoAdder $testFileDataAdder,
        SourceFileFilter $filter,
        iterable $sourceFiles,
        bool $onlyCovered
    ) {
        $this->primaryCoverageProvider = $primaryCoverageProvider;
        $this->testFileDataAdder = $testFileDataAdder;
        $this->filter = $filter;
        $this->sourceFiles = $sourceFiles;
        $this->onlyCovered = $onlyCovered;
    }

    /**
     * @return iterable<SourceFileData>
     */
    public function provideFiles(): iterable
    {
        // TODO: testFileDataAdder could be added to primaryCoverageProvider directory to provide "ready" information right away instead of having to do 2 steps here
        $filesFeed = $this->primaryCoverageProvider->provideFiles();

        $filteredFilesFeed = $this->filter->filter($filesFeed);

        $readyFilesFeed = $this->testFileDataAdder->addTestExecutionInfo($filteredFilesFeed);

        if ($this->onlyCovered === true) {
            // The case where only covered files are considered
            return $readyFilesFeed;
        }

        return $this->appendUncoveredFiles($readyFilesFeed);
    }

    /**
     * Adds to the queue uncovered files found on disk.
     *
     * @param iterable<SourceFileData> $coverage
     *
     * @return iterable<SourceFileData>
     */
    private function appendUncoveredFiles(iterable $coverage): iterable
    {
        $filesSeen = [];

        foreach ($coverage as $data) {
            $filesSeen[$data->getSplFileInfo()->getRealPath()] = self::SEEN;

            yield $data;
        }

        // Since these are sorted sets, there should be a way to optimize.

        foreach ($this->sourceFiles as $splFileInfo) {
            $sourceFilePath = $splFileInfo->getRealPath();

            Assert::string($sourceFilePath);

            if (array_key_exists($sourceFilePath, $filesSeen)) {
                continue;
            }

            yield new SourceFileData($splFileInfo, [new CoverageReport()]);
        }
    }
}
