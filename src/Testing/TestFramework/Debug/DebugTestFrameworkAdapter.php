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

namespace Infection\Testing\TestFramework\Debug;

use function array_key_exists;
use function explode;
use const FILTER_VALIDATE_FLOAT;
use function filter_var;
use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use function Safe\preg_match;

/**
 * @internal
 */
final readonly class DebugTestFrameworkAdapter implements MemoryUsageAware, TestFrameworkAdapter
{
    private const int MEMORY_OUTPUT_PARTS_LIMIT = 2;

    public function __construct(
        private string $runtime,
        private string $logFile,
        private DebugCommandLine $commandLine,
    ) {
    }

    public function getName(): string
    {
        return 'Debug';
    }

    public function testsPass(string $output): bool
    {
        return preg_match('/DEBUG_TEST_FRAMEWORK_PASSED/', $output) === 1;
    }

    public function hasJUnitReport(): bool
    {
        return false;
    }

    public function getInitialTestRunCommandLine(
        string $extraOptions,
        array $phpExtraArgs,
        bool $skipCoverage,
    ): array {
        return $this->commandLine->create(
            runtime: $this->runtime,
            phpArguments: $phpExtraArgs,
            options: [
                'stage' => 'test-framework-initial',
                'log' => $this->logFile,
            ],
        );
    }

    public function getMutantCommandLine(
        array $coverageTests,
        string $mutatedFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions,
    ): array {
        return $this->commandLine->create(
            runtime: $this->runtime,
            phpArguments: [],
            options: [
                'stage' => 'test-framework-mutant',
                'log' => $this->logFile,
                'mutationHash' => $mutationHash,
            ],
        );
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return '';
    }

    public function getMemoryUsed(string $output): float
    {
        $parts = explode('Memory: ', $output, self::MEMORY_OUTPUT_PARTS_LIMIT);

        if (!array_key_exists(1, $parts)) {
            return -1.;
        }

        $memoryParts = explode(' MB', $parts[1], self::MEMORY_OUTPUT_PARTS_LIMIT);
        $memoryUsage = filter_var($memoryParts[0], FILTER_VALIDATE_FLOAT);

        return $memoryUsage === false
            ? -1.
            : $memoryUsage;
    }
}
