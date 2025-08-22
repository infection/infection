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

namespace newSrc\TestFramework\Coverage\JUnit;

use DomainException;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use newSrc\Framework\Filesystem;
use newSrc\TestFramework\Coverage\Locator\NoReportFound;
use newSrc\TestFramework\Coverage\Locator\ReportLocator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use function count;
use function current;
use function implode;
use function iter\map;
use function iter\toArray;
use function sprintf;

/**
 * TODO: heavily copied from IndexXmlCoverageLocator
 * @see IndexXmlCoverageLocator
 * @internal
 */
final readonly class FileReportLocator implements ReportLocator
{
    private const JUNIT_NAME_REGEX = '/^(.+\.)?junit\.xml$/i';

    /**
     * @internal
     */
    public function __construct(
        private Filesystem $filesystem,
        private string     $coverageDirPath,
        private string     $defaultJUnitPath,
    ) {
    }

    public static function create(
        Filesystem $filesystem,
        string $coverageDirPath,
        string $defaultJUnitPath,
    ): self
    {
        return new self(
            $filesystem,
            $coverageDirPath,
            Path::canonicalize($defaultJUnitPath),
        );
    }

    public function locate(): string
    {
        throw new DomainException('TODO: not implemented.');
        if ($this->filesystem->isReadableFile($this->defaultJUnitPath)) {
            return $this->defaultJUnitPath;
        }

        if (!$this->filesystem->isReadableDirectory($this->coverageDirPath)) {
            $this->throwNoCoverageDirectoryFound();
        }

        $files = $this->find();

        if (count($files) > 1) {
            $this->throwAmbiguousFilesFound($files);
        }

        /** @var string|false $report */
        $report = current($files);

        if ($report === false) {
            $this->throwNoReportFound();
        }

        return $report;
    }

    /**
     * @return list<string>
     */
    private function find(): Finder
    {
        return toArray(
            map(
                static fn (SplFileInfo $fileInfo) => Path::canonicalize($fileInfo->getPathname()),
                $this->filesystem
                    ->createFinder()
                    ->files()
                    ->in($this->coverageDirPath)
                    ->name(self::JUNIT_NAME_REGEX)
                    ->sortByName(),
            ),
        );
    }

    /**
     * @throws NoReportFound
     */
    private function throwNoCoverageDirectoryFound(): never
    {
        throw new NoReportFound(
            sprintf(
                'Could not find a JUnit report in "%s": the directory does not exist or is not readable.',
                $this->coverageDirPath,
            ),
        );
    }

    /**
     * @param list<string> $files
     *
     * @throws NoReportFound
     */
    private function throwAmbiguousFilesFound(array $files): never
    {
        throw new NoReportFound(
            sprintf(
                'Could not find a JUnit report in "%s": more than one file with the pattern ".*" has been found. Found: "%s"',
                $this->coverageDirPath,
                implode(
                    '", "',
                    $files,
                ),
            ),
        );
    }

    /**
     * @throws NoReportFound
     */
    private function throwNoReportFound(): never
    {
        throw new NoReportFound(
            sprintf(
                'Could not find a JUnit report in "%s": no file with the pattern ".*" has been found.',
                $this->coverageDirPath,
            ),
        );
    }
}
