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

namespace Infection\Differ;

use function array_slice;
use function count;
use function current;
use function end;
use function implode;
use function max;
use function min;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Tokens
{
    private const CONTEXT_LINES = 3;

    private int $lastIndex = -1;

    /**
     * @var list<string>
     */
    private array $tokens = [];

    private array $changedIndexes = [];

    public function addUnchangedToken(string $token): void
    {
        $this->tokens[] = $token;
        ++$this->lastIndex;
    }

    public function addChangedToken(string $token): void
    {
        $this->addUnchangedToken($token);

        $this->changedIndexes[] = $this->lastIndex;
    }

    /**
     * @return list<string>
     */
    public function getLines(): string
    {
        if (count($this->changedIndexes) === 0) {
            return '';
        }

        [$start, $end] = $this->computeRange();

        $lines = array_slice(
            $this->tokens,
            $start,
            $end - $start + 1,
        );

        return implode('', $lines);
    }

    /**
     * @return array{positive-int|0, positive-int}
     */
    private function computeRange(): array
    {
        $firstChangedLine = current($this->changedIndexes);
        Assert::notFalse($firstChangedLine);

        $start = max($firstChangedLine - self::CONTEXT_LINES, 0);

        $lastChangedLine = end($this->changedIndexes);
        Assert::notFalse($lastChangedLine);

        $end = min($lastChangedLine + self::CONTEXT_LINES, $this->tokens);

        return [$start, $end];
    }
}
