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

namespace Infection\Tests\PhpParser\Visitor\AddTestsVisitor;

use function count;
use Infection\AbstractTestFramework\Coverage\TestLocation;

final class Scenario
{
    public bool $expectedHasTests;

    /**
     * @param list<TestLocation> $traceTests
     * @param list<TestLocation> $expectedTests
     */
    public function __construct(
        public ?bool $isEligible,
        public ?bool $isOnFunctionSignature,
        public array $traceTests,
        public int $expectedTraceCallCount,
        public array $expectedTests,
    ) {
        $this->expectedHasTests = count($this->expectedTests) > 0;
    }

    public function withIsEligible(?bool $isEligible): self
    {
        $clone = clone $this;
        $clone->isEligible = $isEligible;

        return $clone;
    }

    public function withIsOnFunctionSignature(?bool $isOnFunctionSignature): self
    {
        $clone = clone $this;
        $clone->isOnFunctionSignature = $isOnFunctionSignature;

        return $clone;
    }

    public function withExpectedTraceCallCount(int $expectedTraceCallCount): self
    {
        $clone = clone $this;
        $clone->expectedTraceCallCount = $expectedTraceCallCount;

        return $clone;
    }

    /**
     * @param list<TestLocation> $traceTests
     */
    public function withTraceTests(array $traceTests): self
    {
        $clone = clone $this;
        $clone->traceTests = $traceTests;

        return $clone;
    }

    /**
     * @param list<TestLocation> $expectedTests
     */
    public function withExpectedTests(array $expectedTests): self
    {
        $clone = clone $this;
        $clone->expectedTests = $expectedTests;
        $clone->expectedHasTests = count($expectedTests) > 0;

        return $clone;
    }
}
