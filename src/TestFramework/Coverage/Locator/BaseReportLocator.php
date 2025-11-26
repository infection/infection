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

namespace Infection\TestFramework\Coverage\Locator;

use function array_first;
use function count;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use function Pipeline\take;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

/**
 * This class provides the base code for a generic report locator. It does the
 * bulk of the logic but allows the implementing child class to customize
 * how the report should be searched for as well as the exception messages.
 *
 * @internal
 */
abstract class BaseReportLocator implements ReportLocator
{
    private ?string $report = null;

    public function __construct(
        private readonly FileSystem $filesystem,
        private readonly string $sourceDirectory,
        private readonly string $defaultPathname,
    ) {
    }

    final public function locate(): string
    {
        if ($this->report !== null) {
            return $this->report;
        }

        $this->report = $this->filesystem->isReadableFile($this->defaultPathname)
            ? $this->defaultPathname
            : $this->lookup();

        return $this->report;
    }

    public function getDefaultLocation(): string
    {
        return $this->defaultPathname;
    }

    protected function createInvalidReportSource(string $sourceDirectory): InvalidReportSource
    {
        return InvalidReportSource::create($sourceDirectory);
    }

    /**
     * @param list<string> $reportPathnames
     */
    protected function createTooManyReportsFound(
        string $sourceDirectory,
        array $reportPathnames,
    ): TooManyReportsFound {
        return TooManyReportsFound::create($reportPathnames);
    }

    protected function createNoReportFound(string $sourceDirectory): NoReportFound
    {
        return NoReportFound::create($sourceDirectory);
    }

    abstract protected function configureFinder(Finder $finder): void;

    private function lookup(): string
    {
        if (!$this->filesystem->isReadableDirectory($this->sourceDirectory)) {
            throw $this->createInvalidReportSource($this->sourceDirectory);
        }

        // TODO: address this... eventually
        // @phpstan-ignore argument.templateType
        $reportPathnames = take($this->createIndexFinder($this->sourceDirectory))
            ->map(static fn (SplFileInfo $fileInfo) => Path::canonicalize($fileInfo->getPathname()))
            ->toList();

        if (count($reportPathnames) > 1) {
            throw $this->createTooManyReportsFound($this->sourceDirectory, $reportPathnames);
        }

        $report = array_first($reportPathnames);

        if ($report === null) {
            throw $this->createNoReportFound($this->sourceDirectory);
        }

        return $report;
    }

    private function createIndexFinder(string $coverageDirectory): Finder
    {
        $finder = $this->filesystem
            ->createFinder()
            ->files()
            ->in($coverageDirectory);

        $this->configureFinder($finder);

        return $finder;
    }
}
