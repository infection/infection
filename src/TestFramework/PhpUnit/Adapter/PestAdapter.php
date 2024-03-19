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

namespace Infection\TestFramework\PhpUnit\Adapter;

use Infection\AbstractTestFramework\MemoryUsageAware;
use Infection\AbstractTestFramework\SyntaxErrorAware;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\TestFramework\ProvidesInitialRunOnlyOptions;
use function Safe\preg_match;
use function sprintf;

/**
 * @internal
 */
final class PestAdapter implements MemoryUsageAware, ProvidesInitialRunOnlyOptions, SyntaxErrorAware, TestFrameworkAdapter
{
    private const NAME = 'Pest';

    public function __construct(private readonly PhpUnitAdapter $phpUnitAdapter)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function testsPass(string $output): bool
    {
        // Tests:  7 failed
        if (preg_match('/Tests:\s+(.*?)(\d+\sfailed)/i', $output) === 1) {
            return false;
        }

        // Tests:  4 passed
        $isOk = preg_match('/Tests:\s+(.*?)(\d+\spassed)/', $output) === 1;

        // Tests:  1 risked
        $isOkRisked = preg_match('/Tests:\s+(.*?)(\d+\srisked)/', $output) === 1;

        return $isOk || $isOkRisked;
    }

    public function isSyntaxError(string $output): bool
    {
        return preg_match('/(ParseError\s*syntax error|Syntax Error for Pest)/i', $output) === 1;
    }

    public function hasJUnitReport(): bool
    {
        return $this->phpUnitAdapter->hasJUnitReport();
    }

    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage): array
    {
        return $this->phpUnitAdapter->getInitialTestRunCommandLine($extraOptions, $phpExtraArgs, $skipCoverage);
    }

    public function getMutantCommandLine(array $coverageTests, string $mutatedFilePath, string $mutationHash, string $mutationOriginalFilePath, string $extraOptions): array
    {
        return $this->phpUnitAdapter->getMutantCommandLine(
            $coverageTests,
            $mutatedFilePath,
            $mutationHash,
            $mutationOriginalFilePath,
            sprintf('--colors=never %s', $extraOptions),
        );
    }

    public function getVersion(): string
    {
        return $this->phpUnitAdapter->getVersion();
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return $this->phpUnitAdapter->getInitialTestsFailRecommendations($commandLine);
    }

    public function getMemoryUsed(string $output): float
    {
        return $this->phpUnitAdapter->getMemoryUsed($output);
    }

    /**
     * @return string[]
     */
    public function getInitialRunOnlyOptions(): array
    {
        return $this->phpUnitAdapter->getInitialRunOnlyOptions();
    }
}
