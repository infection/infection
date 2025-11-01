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

namespace Infection\Tests\Mutation\MutationBuilder;

use PhpParser\Node;

final readonly class MutationBuilderScenario
{
    /**
     * @param Node[] $expectedOriginalFileAst
     * @param array<string, string|int|float> $expectedAttributes
     * @param array<int, mixed> $expectedTests
     * @param array<int, mixed> $expectedOriginalFileTokens
     */
    private function __construct(
        public readonly MutationBuilder $builder,
        public readonly string $expectedOriginalFilePath,
        public readonly array $expectedOriginalFileAst,
        public readonly string $expectedMutatorClass,
        public readonly string $expectedMutatorName,
        public readonly array $expectedAttributes,
        public readonly string $expectedMutatedNodeClass,
        public readonly array $expectedTests,
        public readonly array $expectedOriginalFileTokens,
        public readonly string $expectedOriginalFileContent,
        public readonly bool $expectedIsCoveredByTest,
    ) {
    }

    /**
     * @param Node[] $expectedOriginalFileAst
     * @param array<string, string|int|float> $expectedAttributes
     * @param array<int, mixed> $expectedTests
     * @param array<int, mixed> $expectedOriginalFileTokens
     */
    public static function create(
        MutationBuilder $builder,
        string $expectedOriginalFilePath,
        array $expectedOriginalFileAst,
        string $expectedMutatorClass,
        string $expectedMutatorName,
        array $expectedAttributes,
        string $expectedMutatedNodeClass,
        array $expectedTests,
        array $expectedOriginalFileTokens,
        string $expectedOriginalFileContent,
        bool $expectedIsCoveredByTest,
    ): self {
        return new self(
            $builder,
            $expectedOriginalFilePath,
            $expectedOriginalFileAst,
            $expectedMutatorClass,
            $expectedMutatorName,
            $expectedAttributes,
            $expectedMutatedNodeClass,
            $expectedTests,
            $expectedOriginalFileTokens,
            $expectedOriginalFileContent,
            $expectedIsCoveredByTest,
        );
    }

    public function withBuilder(MutationBuilder $builder): self
    {
        return new self(
            $builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedOriginalFilePath(string $expectedOriginalFilePath): self
    {
        return new self(
            $this->builder,
            $expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedMutatorClass(string $expectedMutatorClass): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedMutatorName(string $expectedMutatorName): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    /**
     * @param array<string, string|int|float> $expectedAttributes
     */
    public function withExpectedAttributes(array $expectedAttributes): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedMutatedNodeClass(string $expectedMutatedNodeClass): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    /**
     * @param array<int, mixed> $expectedTests
     */
    public function withExpectedTests(array $expectedTests): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedOriginalFileContent(string $expectedOriginalFileContent): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $expectedOriginalFileContent,
            $this->expectedIsCoveredByTest,
        );
    }

    public function withExpectedIsCoveredByTest(bool $expectedIsCoveredByTest): self
    {
        return new self(
            $this->builder,
            $this->expectedOriginalFilePath,
            $this->expectedOriginalFileAst,
            $this->expectedMutatorClass,
            $this->expectedMutatorName,
            $this->expectedAttributes,
            $this->expectedMutatedNodeClass,
            $this->expectedTests,
            $this->expectedOriginalFileTokens,
            $this->expectedOriginalFileContent,
            $expectedIsCoveredByTest,
        );
    }
}
