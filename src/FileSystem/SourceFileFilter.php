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

namespace Infection\FileSystem;

use function array_filter;
use function array_map;
use function explode;
use Infection\FileSystem\Finder\Iterator\RealPathFilterIterator;
use Infection\TestFramework\Coverage\Trace;
use Iterator;
use SplFileInfo;
use Symfony\Component\Finder\Iterator\PathFilterIterator;

/**
 * @internal
 * @final
 */
class SourceFileFilter implements FileFilter
{
    /**
     * @var string[]
     */
    private readonly array $filters;

    /**
     * @param string[] $excludeDirectories
     */
    public function __construct(string $filter, private readonly array $excludeDirectories)
    {
        $this->filters = array_filter(array_map(
            'trim',
            explode(',', $filter),
        ));
    }

    /**
     * Returns a filter array to be used in tests.
     *
     * TODO Good candidate to be refactored into a class.
     *
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function filter(iterable $input): iterable
    {
        $iterator = $this->iterableToIterator($input);

        if ($this->filters !== []) {
            $iterator = new RealPathFilterIterator(
                $iterator,
                $this->filters,
                [],
            );
        }

        if ($this->excludeDirectories !== []) {
            $iterator = new PathFilterIterator($iterator, [], $this->excludeDirectories);
        }

        return $iterator;
    }

    /**
     * @param iterable<SplFileInfo|Trace> $input
     *
     * @return Iterator<SplFileInfo|Trace>
     */
    private function iterableToIterator(iterable $input): Iterator
    {
        if ($input instanceof Iterator) {
            // Generator is an iterator, means most of the time we're done right here.
            return $input;
        }

        /*
         * Clause for all other cases, e.g. when testing.
         *
         * RealPathFilterIterator wants an iterator, not just any iterable or traversable.
         * But not any Traversable is an Iterator. But since we know most of the time we're
         * dealing with Generators, which are instances of an Iterator, we can jump them
         * through. But we may want to use other types of iterables, e.g. arrays in tests,
         * and that last clause is to covert them, or any other non-Iterator object, to
         * a Generator which is an Iterator. Traversable types are complicated, right.
         */
        return (static function () use ($input): Iterator {
            yield from $input;
        })();
    }
}
