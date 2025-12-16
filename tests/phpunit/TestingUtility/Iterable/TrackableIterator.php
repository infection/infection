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

use Iterator;

/**
 * This iterator is a utility to enrich an existing by providing tracking capabilities.
 *
 * It allows tracking the last yielded values without making an assumption about the
 * internal logic of the decorated iterator. This is achieved by tracking the yielded
 * values in separate properties.
 *
 * For example, a custom iterator could have a unique logic in which `::current()` can return
 * the value only once or trigger various side effects. In which case, we cannot use `::current()`
 * to check what the last yielded value was. This class offers a separate property to achieve this.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
final class TrackableIterator implements Iterator
{
    // Value representing the absence of a yielded key. We cannot use `null` as it is a result
    // that may be returned by `::key()`.
    public const EMPTY_KEY = '__θθae5181162f0f0a5daacf223fee61d13d142e276807525867a836d3d6968854a0';

    // Value representing the absence of a yielded value. We cannot use `null` as it is a result
    // that may be returned by `::value()`.
    public const EMPTY_VALUE = '__θθfb31e5bcd01897b407311d26f33b78be5b4604f9199fe6e72240c5aee1a2ee44';

    private bool $yieldedAnyValue = false;

    /**
     * If the input was a list then the index would be matching the current offset we are at.
     * But since the keys may be something else, this is tracked separately.
     *
     * @var positive-int|0
     */
    private int $index = 0;

    /**
     * @var TKey|self::EMPTY_KEY
     */
    private mixed $lastYieldedKey = self::EMPTY_KEY;

    /**
     * @var TValue|self::EMPTY_VALUE
     */
    private mixed $lastYieldedValue = self::EMPTY_VALUE;

    /**
     * @param Iterator<TKey, TValue> $decoratedIterator
     */
    public function __construct(
        private readonly Iterator $decoratedIterator,
    ) {
    }

    public function hasYieldedAnyValue(): bool
    {
        return $this->yieldedAnyValue;
    }

    /**
     * @return positive-int|0
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return TKey|self::EMPTY_KEY
     */
    public function getLastYieldedKey(): mixed
    {
        return $this->lastYieldedKey;
    }

    /**
     * @return TValue|self::EMPTY_VALUE
     */
    public function getLastYieldedValue(): mixed
    {
        return $this->lastYieldedValue;
    }

    public function current(): mixed
    {
        $current = $this->decoratedIterator->current();
        $this->yieldedAnyValue = true;
        $this->lastYieldedValue = $current;

        return $current;
    }

    public function next(): void
    {
        $this->decoratedIterator->next();

        ++$this->index;
    }

    public function key(): mixed
    {
        $key = $this->decoratedIterator->key();

        $this->yieldedAnyValue = true;
        $this->lastYieldedKey = $key;

        return $key;
    }

    public function valid(): bool
    {
        return $this->decoratedIterator->valid();
    }

    public function rewind(): void
    {
        $this->decoratedIterator->rewind();

        $this->index = 0;
    }
}
