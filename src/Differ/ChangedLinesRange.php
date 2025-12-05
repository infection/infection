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

use Webmozart\Assert\Assert;

/**
 * Inclusive segment of the changed lines.
 *
 * @internal
 */
final readonly class ChangedLinesRange
{
    /**
     * @param positive-int|0 $startLine
     * @param positive-int|0 $endLine
     */
    private function __construct(
        public int $startLine,
        public int $endLine,
    ) {
    }

    /**
     * @param positive-int|0 $startLine
     * @param positive-int|0 $endLine
     */
    public static function create(
        int $startLine,
        int $endLine,
    ): self {
        Assert::lessThanEq(value: $startLine, limit: $endLine);

        return new self($startLine, $endLine);
    }

    /**
     * @param positive-int|0 $line
     */
    public static function forLine(int $line): self
    {
        return new self($line, $line);
    }

    /**
     * For example, in a GNU diff, "12,7" means the lines [12,18] (7 lines)
     * changed.
     *
     * @param positive-int|0 $startLine
     * @param positive-int $newCount Span of the change, starting at the start line.
     */
    public static function forRange(int $startLine, int $newCount): self
    {
        $endLine = $startLine + $newCount - 1;

        Assert::lessThanEq(value: $startLine, limit: $endLine);

        return new self($startLine, $endLine);
    }

    /**
     * @param positive-int|0 $startLine
     * @param positive-int|0 $endLine
     */
    public function touches(int $startLine, int $endLine): bool
    {
        return $endLine >= $this->startLine
            && $startLine <= $this->endLine;
    }
}
