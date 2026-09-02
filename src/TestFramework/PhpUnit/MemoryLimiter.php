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

namespace Infection\TestFramework\PhpUnit;

use function sprintf;

/**
 * Derives PHP arguments limiting PHPUnit mutant processes to twice the memory used by the initial test suite.
 * It preserves an existing memory limit and avoids changing the system PHP configuration.
 *
 * @internal
 */
final class MemoryLimiter
{
    private const int MEMORY_LIMIT_MULTIPLIER = 2;

    /**
     * @var float|null Memory usage in megabytes.
     */
    private ?float $memoryUsage = null;

    public function __construct(
        private readonly MemoryLimiterEnvironment $environment,
    ) {
    }

    /**
     * @param float|null $memoryUsage Memory usage in megabytes.
     */
    public function recordInitialRunMemoryUsage(?float $memoryUsage): void
    {
        $this->memoryUsage = $memoryUsage;
    }

    /**
     * @return list<string>
     */
    public function getPhpExtraArguments(): array
    {
        if (
            $this->memoryUsage === null
            || $this->environment->hasMemoryLimitSet()
        ) {
            return [];
        }

        return [
            '-d',
            sprintf(
                'memory_limit=%dM',
                (int) ($this->memoryUsage * self::MEMORY_LIMIT_MULTIPLIER),
            ),
        ];
    }
}
