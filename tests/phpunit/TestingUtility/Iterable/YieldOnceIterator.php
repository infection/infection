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

namespace Infection\Tests\TestingUtility\Iterable;

use DomainException;
use Iterator;

/**
 * This iterator is a utility to enrich an existing Iterator.
 *
 * It decorates an iterator and ensures the keys and values can be
 *  yielded once and only once, unless the iterator is rewind.
 *
 * This aims to mimic an iterable for which the fetching item
 * operation is heavy, hence should not be done more than once, but
 * for which no caching is done.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
final class YieldOnceIterator implements Iterator
{
    private bool $yieldedKey = false;

    private bool $yieldedValue = false;

    /**
     * @param Iterator<TKey, TValue> $decoratedIterator
     */
    public function __construct(
        private readonly Iterator $decoratedIterator,
    ) {
    }

    public function current(): mixed
    {
        if ($this->yieldedValue) {
            throw new DomainException('The current value cannot be retrieved more than once.');
        }

        $current = $this->decoratedIterator->current();
        $this->yieldedValue = true;

        return $current;
    }

    public function next(): void
    {
        $this->decoratedIterator->next();

        $this->reset();
    }

    public function key(): mixed
    {
        if ($this->yieldedKey) {
            throw new DomainException('The current key cannot be retrieved more than once.');
        }

        $key = $this->decoratedIterator->key();
        $this->yieldedKey = true;

        return $key;
    }

    public function valid(): bool
    {
        return $this->decoratedIterator->valid();
    }

    public function rewind(): void
    {
        $this->decoratedIterator->rewind();

        $this->reset();
    }

    private function reset(): void
    {
        $this->yieldedKey = false;
        $this->yieldedValue = false;
    }
}
