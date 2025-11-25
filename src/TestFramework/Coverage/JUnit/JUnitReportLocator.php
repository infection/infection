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

namespace Infection\TestFramework\Coverage\JUnit;

use function array_map;
use function count;
use function current;
use const DIRECTORY_SEPARATOR;
use function file_exists;
use function implode;
use Infection\TestFramework\Coverage\Locator\Throwable\InvalidReportSource;
use Infection\TestFramework\Coverage\Locator\Throwable\NoReportFound;
use Infection\TestFramework\Coverage\Locator\Throwable\TooManyReportsFound;
use function is_dir;
use function is_readable;
use function iterator_to_array;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 * @final
 */
class JUnitReportLocator
{
    public const JUNIT_FILENAME_REGEX = '/^(.+\.)?junit\.xml$/i';

    private const DEFAULT_JUNIT_FILENAME = 'junit.xml';

    private ?string $jUnitPath = null;

    public function __construct(
        private readonly string $coveragePath,
        private readonly string $defaultJUnitPath,
    ) {
    }

    public static function create(
        string $coverageDirectory,
        ?string $defaultJUnitPathname = null,
    ): self {
        return new self(
            $coverageDirectory,
            $defaultJUnitPathname === null
                ? self::createPHPUnitDefaultJUnitPathname($coverageDirectory)
                : Path::canonicalize($defaultJUnitPathname),
        );
    }

    public function getDefaultLocation(): string
    {
        return $this->defaultJUnitPath;
    }

    /**
     * @throws InvalidReportSource
     * @throws TooManyReportsFound
     * @throws NoReportFound
     */
    public function locate(): string
    {
        if ($this->jUnitPath !== null) {
            return $this->jUnitPath;
        }

        // This is the JUnit path enforced before. It is also the one recommended by the
        // CoverageChecker hence it makes sense to try this one first before attempting any more
        // expensive lookup
        if (file_exists($this->defaultJUnitPath)) {
            return $this->jUnitPath = $this->defaultJUnitPath;
        }

        if (!file_exists($this->coveragePath)
            || !is_readable($this->coveragePath)
            || !is_dir($this->coveragePath)
        ) {
            throw new InvalidReportSource(
                sprintf(
                    'Could not find the JUnit report in "%s": the pathname is not a valid or readable directory.',
                    $this->coveragePath,
                ),
            );
        }

        $files = iterator_to_array(
            Finder::create()
                ->files()
                ->in($this->coveragePath)
                ->name(self::JUNIT_FILENAME_REGEX)
                ->sortByName(),
            false,
        );

        if (count($files) > 1) {
            $pathnames = array_map(
                static fn (SplFileInfo $fileInfo): string => Path::canonicalize($fileInfo->getPathname()),
                $files,
            );

            throw new TooManyReportsFound(
                sprintf(
                    'Could not find the JUnit report in "%s": more than one file with the pattern "%s" was found. Found: "%s".',
                    $this->coveragePath,
                    self::JUNIT_FILENAME_REGEX,
                    implode(
                        '", "',
                        $pathnames,
                    ),
                ),
            );
        }

        $junitFileInfo = current($files);

        if ($junitFileInfo !== false) {
            return $this->jUnitPath = Path::canonicalize($junitFileInfo->getPathname());
        }

        throw new NoReportFound(
            sprintf(
                'Could not find the JUnit report in "%s": no file with the pattern "%s" was found.',
                $this->coveragePath,
                self::JUNIT_FILENAME_REGEX,
            ),
        );
    }

    private static function createPHPUnitDefaultJUnitPathname(string $coverageDirectory): string
    {
        return Path::canonicalize($coverageDirectory . DIRECTORY_SEPARATOR . self::DEFAULT_JUNIT_FILENAME);
    }
}
