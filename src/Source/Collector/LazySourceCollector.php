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

use Symfony\Component\Finder\SplFileInfo;

/**
 * Decorator for SourceCollector that lazily collects and caches source files.
 *
 * On the first call to collect(), it yields items from the decorated collector
 * while building a cache. Once the iteration completes, later calls return
 * the cached array directly, avoiding a repeated collection.
 *
 * This is useful when the decorated collector performs expensive operations
 * (e.g. file system traversal, git operations) that should only happen once.
 *
 * @internal
 */
final class LazySourceCollector implements SourceCollector
{
    /**
     * @var list<SplFileInfo>|null
     */
    private ?array $cachedSourceFiles = null;

    public function __construct(
        private readonly SourceCollector $decoratedCollector,
    ) {
    }

    public function isFiltered(): bool
    {
        return $this->decoratedCollector->isFiltered();
    }

    /**
     * @return iterable<SplFileInfo>
     */
    public function collect(): iterable
    {
        if ($this->cachedSourceFiles !== null) {
            return $this->cachedSourceFiles;
        }

        return $this->collectAndCache();
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function collectAndCache(): iterable
    {
        $cache = [];

        foreach ($this->decoratedCollector->collect() as $file) {
            $cache[] = $file;

            yield $file;
        }

        $this->cachedSourceFiles = $cache;
    }
}
