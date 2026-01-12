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

namespace Infection\Tests\TestFramework\Tracing\Trace;

use DomainException;
use Infection\TestFramework\Tracing\Trace\NodeLineRangeData;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use SplFileInfo;

/**
 * Represents a Trace state with any dynamic behaviour or laziness of any kind.
 * This is mostly useful for testing purposes where we want to declare an
 * expected Trace state.
 */
final readonly class SyntheticTrace implements Trace
{
    public function __construct(
        public SplFileInfo $sourceFileInfo,
        public string $realPath,
        public string $relativePathname,
        public bool $hasTest,
        public TestLocations $tests,
    ) {
    }

    public function getSourceFileInfo(): SplFileInfo
    {
        return $this->sourceFileInfo;
    }

    public function getRealPath(): string
    {
        return $this->realPath;
    }

    public function hasTests(): bool
    {
        return $this->hasTest;
    }

    public function getTests(): TestLocations
    {
        return $this->tests;
    }

    public function getAllTestsForMutation(
        NodeLineRangeData $lineRange,
        bool $isOnFunctionSignature,
    ): iterable {
        throw new DomainException('Not implemented.');
    }
}
