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

namespace Infection\Mutant;

use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver;
use Override;

/**
 * Accounts for coverage and the expected test runtime when resolving detection status.
 *
 * @internal
 */
final readonly class TestSuiteMutantDetectionStatusResolver implements MutantProcessDetectionStatusResolver
{
    public function __construct(
        private MutantProcessDetectionStatusResolver $resolver,
        private Mutant $mutant,
        private ?float $timeout,
    ) {
    }

    #[Override]
    public function resolve(string $stdout, ?int $exitCode, bool $timedOut): DetectionStatus
    {
        if (!$this->mutant->isCoveredByTest()) {
            return DetectionStatus::NOT_COVERED;
        }

        // If only some tests fit the budget, a timeout does not establish an infinite loop.
        if ($timedOut
            && $this->timeout !== null
            && $this->mutant->getMutation()->getNominalTestExecutionTime() >= $this->timeout
        ) {
            return DetectionStatus::SKIPPED;
        }

        return $this->resolver->resolve($stdout, $exitCode, $timedOut);
    }
}
