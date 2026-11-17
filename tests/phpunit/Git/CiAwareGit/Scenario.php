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

namespace Infection\Tests\Git\CiAwareGit;

use Psr\Log\Test\TestLogger;

/**
 * @phpstan-import-type log_record_array from TestLogger
 */
final class Scenario
{
    /**
     * @param array<string, string> $environment
     * @param list<log_record_array> $expectedRecords
     */
    public function __construct(
        public array $environment,
        public ?string $event,
        public string $expected,
        public array $expectedRecords,
    ) {
    }

    /**
     * @param array<string, string> $environment
     */
    public function withEnvironment(array $environment): self
    {
        $clone = clone $this;
        $clone->environment = $environment;

        return $clone;
    }

    public function withEvent(?string $event): self
    {
        $clone = clone $this;
        $clone->event = $event;

        return $clone;
    }

    public function withExpected(string $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }

    /**
     * @param list<log_record_array> $expectedRecords
     */
    public function withExpectedRecords(array $expectedRecords): self
    {
        $clone = clone $this;
        $clone->expectedRecords = $expectedRecords;

        return $clone;
    }
}
