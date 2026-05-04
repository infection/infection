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

namespace Infection\Tests\PhpParser\Visitor\ExcludeUnchangedLinesVisitor;

use function array_values;
use Infection\Differ\ChangedLinesRange;

final class Scenario
{
    /**
     * @param list<int>|null $eligibleNodeIds
     * @param list<int>|null $ineligibleNodeIds
     * @param list<ChangedLinesRange> $changedLineRanges
     */
    public function __construct(
        public string $code,
        public ?array $eligibleNodeIds,
        public ?array $ineligibleNodeIds,
        public array $changedLineRanges,
        public string $expected,
    ) {
    }

    public static function create(): self
    {
        return new self(
            code: '',
            eligibleNodeIds: [],
            ineligibleNodeIds: [],
            changedLineRanges: [],
            expected: '',
        );
    }

    public function withCode(string $code): self
    {
        $clone = clone $this;
        $clone->code = $code;

        return $clone;
    }

    /**
     * @param list<int>|null $eligibleNodeIds
     */
    public function withEligibleNodeIds(?array $eligibleNodeIds = null): self
    {
        $clone = clone $this;
        $clone->eligibleNodeIds = $eligibleNodeIds;

        return $clone;
    }

    /**
     * @param list<int>|null $ineligibleNodeIds
     */
    public function withIneligibleNodeIds(?array $ineligibleNodeIds = null): self
    {
        $clone = clone $this;
        $clone->ineligibleNodeIds = $ineligibleNodeIds;

        return $clone;
    }

    public function withChangedLineRanges(ChangedLinesRange ...$changedLineRanges): self
    {
        $clone = clone $this;
        $clone->changedLineRanges = array_values($changedLineRanges);

        return $clone;
    }

    public function withExpected(string $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }
}
