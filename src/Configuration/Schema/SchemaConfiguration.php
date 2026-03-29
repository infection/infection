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

namespace Infection\Configuration\Schema;

use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\TestFrameworkTypes;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class SchemaConfiguration
{
    /**
     * @param non-empty-string $pathname
     * @param array<string, mixed> $mutators
     * @param TestFrameworkTypes::*|null $testFramework
     * @param StaticAnalysisToolTypes::*|null $staticAnalysisTool
     */
    public function __construct(
        public string $pathname,
        public ?float $timeout,
        public Source $source,
        public Logs $logs,
        public ?string $tmpDir,
        public PhpUnit $phpUnit,
        public PhpStan $phpStan,
        public ?bool $ignoreMsiWithNoMutations,
        public ?float $minMsi,
        public ?float $minCoveredMsi,
        public ?bool $timeoutsAsEscaped,
        public ?int $maxTimeouts,
        public array $mutators,
        public ?string $testFramework,
        public ?string $bootstrap,
        public ?string $initialTestsPhpOptions,
        public ?string $testFrameworkExtraOptions,
        public ?string $staticAnalysisToolOptions,
        public string|int|null $threads,
        public ?string $staticAnalysisTool,
    ) {
        Assert::nullOrGreaterThanEq($timeout, 0);
        Assert::nullOrOneOf($testFramework, TestFrameworkTypes::getTypes());
        Assert::nullOrOneOf($staticAnalysisTool, StaticAnalysisToolTypes::getTypes());
    }
}
