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

namespace Infection\Source\Collector;

use function array_map;
use function count;
use function dirname;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use Infection\Source\Exception\NoSourceFound;
use Iterator;
use function Pipeline\take;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Iterator\PathFilterIterator;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final readonly class BasicSourceCollector implements SourceCollector
{
    /**
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedFilesOrDirectories
     */
    public function __construct(
        private array $sourceDirectories,
        private array $excludedFilesOrDirectories,
        private ?PlainFilter $filter,
    ) {
    }

    public function collect(): array
    {
        $files = $this->doCollect();

        if (count($files) === 0) {
            throw NoSourceFound::noSourceFileFound($this->filter);
        }

        return $files;
    }

    /**
     * @param non-empty-string $configurationPathname
     * @param non-empty-string[] $sourceDirectories
     * @param non-empty-string[] $excludedFilesOrDirectories
     */
    public static function create(
        string $configurationPathname,
        array $sourceDirectories,
        array $excludedFilesOrDirectories,
        ?PlainFilter $filter,
    ): self {
        $configurationDirname = dirname($configurationPathname);

        return new self(
            // We need to make the source file paths absolute, otherwise the
            // collector will collect the files relative to the current working
            // directory instead of relative to the location of the configuration
            // file.
            self::makePathsAbsolute($configurationDirname, $sourceDirectories),
            $excludedFilesOrDirectories,
            $filter,
        );
    }

    /**
     * @return SplFileInfo[]
     */
    private function doCollect(): array
    {
        if ($this->sourceDirectories === []) {
            return [];
        }

        $iterator = $this->filter(
            $this
                ->createFinder()
                ->getIterator(),
        );

        return take($iterator)->toList();
    }

    /**
     * @param non-empty-string $configurationDirname
     * @param non-empty-string[] $sourceDirectories
     *
     * @return non-empty-string[]
     */
    private static function makePathsAbsolute(
        string $configurationDirname,
        array $sourceDirectories,
    ): array {
        $mapToAbsolutePath = static fn (string $path) => Path::isAbsolute($path)
            ? $path
            : Path::join(
                $configurationDirname,
                $path,
            );

        return array_map(
            $mapToAbsolutePath(...),
            $sourceDirectories,
        );
    }

    private function createFinder(): Finder
    {
        return Finder::create()
            ->in($this->sourceDirectories)
            ->exclude($this->excludedFilesOrDirectories)
            ->notPath($this->excludedFilesOrDirectories)
            ->files()
            ->name('*.php');
    }

    /**
     * @param Iterator<SplFileInfo> $iterator
     *
     * @return Iterator<SplFileInfo>
     */
    private function filter(Iterator $iterator): Iterator
    {
        // TODO: could use Finder::setFilter() instead!
        if ($this->filter !== null) {
            $iterator = new RealPathFilterIterator(
                $iterator,
                $this->filter->values,
                [],
            );
        }

        // TODO: to check if we really need to re-apply the excluded files.
        //   could be necessary due to a Finder bug for example.
        if (count($this->excludedFilesOrDirectories) !== 0) {
            $iterator = new PathFilterIterator($iterator, [], $this->excludedFilesOrDirectories);
        }

        return $iterator;
    }
}
