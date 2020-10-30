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

namespace Infection\Mutation;

use function array_intersect_key;
use function array_keys;
use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\ProfileList;
use Infection\PhpParser\MutatedNode;
use Infection\TestFramework\Coverage\JUnit\JUnitTestCaseTimeAdder;
use function md5;
use PhpParser\Node;
use function Safe\array_flip;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class Mutation
{
    private string $originalFilePath;
    private string $mutatorName;
    private string $mutatedNodeClass;
    private MutatedNode $mutatedNode;
    private int $mutationByMutatorIndex;
    /** @var array<string|int|float> */
    private array $attributes;
    /** @var Node[] */
    private array $originalFileAst;
    /** @var TestLocation[] */
    private array $tests;
    private bool $coveredByTests;
    private ?float $nominalTimeToTest = null;

    private ?string $hash = null;

    /**
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param TestLocation[] $tests
     */
    public function __construct(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorName,
        array $attributes,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        int $mutationByMutatorIndex,
        array $tests
    ) {
        Assert::oneOf($mutatorName, array_keys(ProfileList::ALL_MUTATORS));

        foreach (MutationAttributeKeys::ALL as $key) {
            Assert::keyExists($attributes, $key);
        }

        $this->originalFilePath = $originalFilePath;
        $this->originalFileAst = $originalFileAst;
        $this->mutatorName = $mutatorName;
        $this->attributes = array_intersect_key($attributes, array_flip(MutationAttributeKeys::ALL));
        $this->mutatedNodeClass = $mutatedNodeClass;
        $this->mutatedNode = $mutatedNode;
        $this->mutationByMutatorIndex = $mutationByMutatorIndex;
        $this->tests = $tests;
        $this->coveredByTests = $tests !== [];
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    /**
     * @return Node[]
     */
    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }

    public function getMutatorName(): string
    {
        return $this->mutatorName;
    }

    /**
     * @return (string|int|float)[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getOriginalStartingLine(): int
    {
        return (int) $this->attributes['startLine'];
    }

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }

    public function getMutatedNode(): MutatedNode
    {
        return $this->mutatedNode;
    }

    // TODO: hasTest()?
    public function isCoveredByTest(): bool
    {
        return $this->coveredByTests;
    }

    /**
     * @return TestLocation[]
     */
    public function getAllTests(): array
    {
        return $this->tests;
    }

    /**
     * Overall time needed to run known tests for a mutation, excluding dependencies.
     */
    public function getNominalTestExecutionTime(): float
    {
        // TestLocator returns non-unique tests, and JUnitTestCaseSorter works around that; we have to do that too.
        return $this->nominalTimeToTest ?? $this->nominalTimeToTest = (new JUnitTestCaseTimeAdder($this->tests))->getTotalTestTime();
    }

    public function getHash(): string
    {
        return $this->hash ?? $this->hash = $this->createHash();
    }

    private function createHash(): string
    {
        $hashKeys = [
            $this->originalFilePath,
            $this->mutatorName,
            $this->mutationByMutatorIndex,
        ];

        foreach ($this->attributes as $attribute) {
            $hashKeys[] = $attribute;
        }

        return md5(implode('_', $hashKeys));
    }
}
