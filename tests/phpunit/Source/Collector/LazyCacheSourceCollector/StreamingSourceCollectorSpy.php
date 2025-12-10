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

namespace Infection\Tests\Source\Collector\LazyCacheSourceCollector;

use DomainException;
use Infection\Source\Collector\SourceCollector;
use Iterator;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Test double that tracks when items are yielded.
 * @internal
 */
final class StreamingSourceCollectorSpy implements SourceCollector
{
    public int $yieldCount = 0;

    /**
     * @param SplFileInfo[] $files
     */
    public function __construct(
        private readonly array $files,
    ) {
    }

    public function isFiltered(): bool
    {
        throw new DomainException('Not implemented.');
    }

    // We constrained it to an Iterator here because otherwise it's a pain the
    // a** to test.
    // Indeed, we do not have all the necessary, humungous and tedious plugin
    // to deal with `iterable`.
    /**
     * @return Iterator<array-key, SplFileInfo>
     */
    public function collect(): Iterator
    {
        foreach ($this->files as $key => $file) {
            ++$this->yieldCount;

            yield $key => $file;
        }
    }
}
