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

use function array_map;
use function count;
use function current;
use function file_exists;
use function implode;
use Infection\FileSystem\Locator\FileNotFound;
use function iterator_to_array;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 * @final
 */
class IndexXmlCoverageLocator
{
    private readonly string $defaultIndexPath;

    private ?string $indexPath = null;

    public function __construct(private readonly string $coveragePath)
    {
        $this->defaultIndexPath = Path::canonicalize($coveragePath . '/coverage-xml/index.xml');
    }

    /**
     * @throws FileNotFound
     */
    public function locate(): string
    {
        if ($this->indexPath !== null) {
            return $this->indexPath;
        }

        // This is the index path enforced before. It is also the one recommended by the
        // CoverageChecker hence it makes sense to try this one first before attempting any more
        // expensive lookup
        if (file_exists($this->defaultIndexPath)) {
            return $this->indexPath = $this->defaultIndexPath;
        }

        if (!file_exists($this->coveragePath)) {
            throw new FileNotFound(sprintf(
                'Could not find any "index.xml" file in "%s"',
                $this->coveragePath,
            ));
        }

        $files = iterator_to_array(
            Finder::create()
                ->files()
                ->in($this->coveragePath)
                ->name('/^index\.xml$/i')
                ->sortByName(),
            false,
        );

        if (count($files) > 1) {
            throw new FileNotFound(sprintf(
                'Could not locate the XML coverage index file. More than one file has been '
                . 'found: "%s"',
                implode(
                    '", "',
                    array_map(
                        static fn (SplFileInfo $fileInfo): string => Path::canonicalize($fileInfo->getPathname()),
                        $files,
                    ),
                ),
            ));
        }

        $indexFileInfo = current($files);

        if ($indexFileInfo !== false) {
            return $this->indexPath = Path::canonicalize($indexFileInfo->getPathname());
        }

        throw new FileNotFound(sprintf(
            'Could not find any "index.xml" file in "%s"',
            $this->coveragePath,
        ));
    }
}
