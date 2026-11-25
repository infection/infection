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

namespace Infection\TestFramework\Legacy;

use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\TestFramework\Contracts\Mutant\MutantProcessDetectionStatusResolver;
use Override;

/**
 * @internal
 */
final readonly class LegacyMutantDetectionStatusResolver implements MutantProcessDetectionStatusResolver
{
    private const int PROCESS_MIN_ERROR_CODE = 100;

    public function __construct(private TestFrameworkAdapter $adapter)
    {
    }

    #[Override]
    public function resolve(string $stdout, ?int $exitCode, bool $timedOut): DetectionStatus
    {
        if ($timedOut) {
            return DetectionStatus::TIMED_OUT;
        }

        if ($exitCode !== null
            && $exitCode > self::PROCESS_MIN_ERROR_CODE
        ) {
            return DetectionStatus::ERROR;
        }

        if ($exitCode === 0
            && $this->adapter->testsPass($stdout)
        ) {
            return DetectionStatus::ESCAPED;
        }

        if (!$this->adapter instanceof SyntaxErrorAware) {
            return DetectionStatus::KILLED_BY_TESTS;
        }

        if ($this->adapter->isSyntaxError($stdout)) {
            return DetectionStatus::SYNTAX_ERROR;
        }

        return DetectionStatus::KILLED_BY_TESTS;
    }
}
