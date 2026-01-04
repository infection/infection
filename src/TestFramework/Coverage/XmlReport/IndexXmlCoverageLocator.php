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

namespace Infection\TestFramework\Coverage\XmlReport;

use const DIRECTORY_SEPARATOR;
use function implode;
use Infection\FileSystem\FileSystem;
use Infection\TestFramework\Coverage\Locator\BaseReportLocator;
use Infection\TestFramework\Coverage\Locator\ReportLocator;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
final class IndexXmlCoverageLocator extends BaseReportLocator implements ReportLocator
{
    public const INDEX_FILENAME_REGEX = '/^index\.xml$/i';

    private const DEFAULT_INDEX_RELATIVE_PATHNAME = 'coverage-xml/index.xml';

    public static function create(
        FileSystem $filesystem,
        string $coverageDirectory,
        ?string $defaultPHPUnitXmlCoverageIndexPathname = null,
    ): self {
        return new self(
            $filesystem,
            $coverageDirectory,
            $defaultPHPUnitXmlCoverageIndexPathname === null
                ? self::createPHPUnitDefaultCoverageXmlIndexPathname($coverageDirectory)
                : Path::canonicalize($defaultPHPUnitXmlCoverageIndexPathname),
        );
    }

    protected function createInvalidReportSource(string $coverageDirectory): InvalidReportSource
    {
        return new InvalidReportSource(
            sprintf(
                'Could not find the XML coverage index report in "%s": the pathname is not a valid or readable directory.',
                $coverageDirectory,
            ),
        );
    }

    protected function createTooManyReportsFound(
        string $coverageDirectory,
        array $reportPathnames,
    ): TooManyReportsFound {
        return new TooManyReportsFound(
            sprintf(
                'Could not find the XML coverage index report in "%s": more than one file with the pattern "%s" was found. Found: "%s".',
                $coverageDirectory,
                self::INDEX_FILENAME_REGEX,
                implode(
                    '", "',
                    $reportPathnames,
                ),
            ),
            reportPathnames: $reportPathnames,
        );
    }

    protected function createNoReportFound(string $coverageDirectory): NoReportFound
    {
        return new NoReportFound(
            sprintf(
                'Could not find the XML coverage index report in "%s": no file with the pattern "%s" was found.',
                $coverageDirectory,
                self::INDEX_FILENAME_REGEX,
            ),
        );
    }

    protected function configureFinder(Finder $finder): void
    {
        // TODO: should the file depth be limited too?
        $finder
            ->name(self::INDEX_FILENAME_REGEX)
            // We sort by name for deterministic results. It has no impact on
            // the happy path as we expect to find only one file.
            // In the other scenario, this gives a more consistent result to the
            // user for a failing scenario.
            // This also makes testing easier.
            ->sortByName();
    }

    private static function createPHPUnitDefaultCoverageXmlIndexPathname(string $coverageDirectory): string
    {
        return Path::canonicalize($coverageDirectory . DIRECTORY_SEPARATOR . self::DEFAULT_INDEX_RELATIVE_PATHNAME);
    }
}
