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

namespace Infection\Tests\TestFramework\PhpUnit\Adapter\PhpUnitAdapter;

use Infection\TestFramework\MapSourceClassToTestStrategy;
use SplFileInfo;

final class InitialTestRunScenario
{
    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     * @param MapSourceClassToTestStrategy::*|null $mapSourceClassToTestStrategy
     * @param list<string> $phpExtraArgs
     * @param list<string> $expected
     */
    public function __construct(
        public string $testFrameworkConfigContent,
        public string $version,
        public array $filteredSourceFilesToMutate,
        public bool $executeOnlyCoveringTestCases,
        public ?string $mapSourceClassToTestStrategy,
        public string $extraOptions,
        public array $phpExtraArgs,
        public bool $skipCoverage,
        public string $pcovDirectory,
        public array $expected,
    ) {
    }

    public function withTestFrameworkConfigContent(string $testFrameworkConfigContent): self
    {
        $clone = clone $this;
        $clone->testFrameworkConfigContent = $testFrameworkConfigContent;

        return $clone;
    }

    public function withVersion(string $version): self
    {
        $clone = clone $this;
        $clone->version = $version;

        return $clone;
    }

    /**
     * @param SplFileInfo[] $filteredSourceFilesToMutate
     */
    public function withFilteredSourceFilesToMutate(array $filteredSourceFilesToMutate): self
    {
        $clone = clone $this;
        $clone->filteredSourceFilesToMutate = $filteredSourceFilesToMutate;

        return $clone;
    }

    public function withExecuteOnlyCoveringTestCases(bool $executeOnlyCoveringTestCases): self
    {
        $clone = clone $this;
        $clone->executeOnlyCoveringTestCases = $executeOnlyCoveringTestCases;

        return $clone;
    }

    /**
     * @param MapSourceClassToTestStrategy::*|null $mapSourceClassToTestStrategy
     */
    public function withMapSourceClassToTestStrategy(?string $mapSourceClassToTestStrategy): self
    {
        $clone = clone $this;
        $clone->mapSourceClassToTestStrategy = $mapSourceClassToTestStrategy;

        return $clone;
    }

    public function withExtraOptions(string $extraOptions): self
    {
        $clone = clone $this;
        $clone->extraOptions = $extraOptions;

        return $clone;
    }

    /**
     * @param list<string> $phpExtraArgs
     */
    public function withPhpExtraArgs(array $phpExtraArgs): self
    {
        $clone = clone $this;
        $clone->phpExtraArgs = $phpExtraArgs;

        return $clone;
    }

    public function withSkipCoverage(bool $skipCoverage): self
    {
        $clone = clone $this;
        $clone->skipCoverage = $skipCoverage;

        return $clone;
    }

    public function withPcovDirectory(string $pcovDirectory): self
    {
        $clone = clone $this;
        $clone->pcovDirectory = $pcovDirectory;

        return $clone;
    }

    /**
     * @param list<string> $expected
     */
    public function withExpected(array $expected): self
    {
        $clone = clone $this;
        $clone->expected = $expected;

        return $clone;
    }
}
