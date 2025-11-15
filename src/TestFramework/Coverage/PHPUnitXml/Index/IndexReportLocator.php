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

namespace Infection\TestFramework\Coverage\PHPUnitXml\Index;

use function count;
use function current;
use function implode;
use Infection\FileSystem\Filesystem;
use Infection\TestFramework\Coverage\Locator\Exception\NoReportFound;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\TestFramework\Coverage\XmlReport\IndexXmlCoverageLocator;
use function Pipeline\take;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

/**
 * TODO: heavily copied from IndexXmlCoverageLocator
 * @see IndexXmlCoverageLocator
 * @internal
 */
final readonly class IndexReportLocator implements ReportLocator
{
    private const INDEX_NAME_REGEX = '/^index\.xml$/i';

    /**
     * @internal
     */
    public function __construct(
        private Filesystem $filesystem,
        private string $coverageDirPath,
        private string $defaultCoverageXmlIndexPath,
    ) {
    }

    public static function create(
        Filesystem $filesystem,
        string $coverageDirPath,
        ?string $defaultCoverageXmlIndexPath = null,
    ): self {
        return new self(
            $filesystem,
            Path::canonicalize($coverageDirPath),
            $defaultCoverageXmlIndexPath === null
                ? self::createPHPUnitDefaultCoverageXmlIndexPath($coverageDirPath)
                : Path::canonicalize($defaultCoverageXmlIndexPath),
        );
    }

    public static function createPHPUnitDefaultCoverageXmlIndexPath(string $coverageDirPath): string
    {
        return Path::canonicalize($coverageDirPath . '/coverage-xml/index.xml');
    }

    public function locate(): string
    {
        if ($this->filesystem->isReadableFile($this->defaultCoverageXmlIndexPath)) {
            return $this->defaultCoverageXmlIndexPath;
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
    private function find(): array
    {
        return take($this->createIndexFinder())
            ->map(Filesystem::mapFileInfoToCanonicalPathname(...))
            ->toList();
    }

    private function createIndexFinder(): Finder
    {
        return $this->filesystem
            ->createFinder()
            ->files()
            ->in($this->coverageDirPath)
            ->name(self::INDEX_NAME_REGEX)
            ->sortByName();
    }

    /**
     * @throws NoReportFound
     */
    private function throwNoCoverageDirectoryFound(): never
    {
        throw new NoReportFound(
            sprintf(
                'Could not find a coverage XML index report in "%s": the directory does not exist or is not readable.',
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
                'Could not find a coverage XML index report in "%s": more than one file with the pattern "%s" has been found. Found: "%s".',
                $this->coverageDirPath,
                self::INDEX_NAME_REGEX,
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
                'Could not find a coverage XML index report in "%s": no file with the pattern "%s" has been found.',
                $this->coverageDirPath,
                self::INDEX_NAME_REGEX,
            ),
        );
    }
}
