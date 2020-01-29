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

use function array_flip;
use function array_intersect_key;
use function array_keys;
use function count;
use function implode;
use Infection\AbstractTestFramework\Coverage\CoverageLineData;
use Infection\Mutator\ProfileList;
use Infection\PhpParser\MutatedNode;
use function md5;
use PhpParser\Node;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class Mutation
{
    public const ATTRIBUTE_KEYS = [
        'startLine',
        'endLine',
        'startTokenPos',
        'endTokenPos',
        'startFilePos',
        'endFilePos',
    ];

    private $originalFilePath;
    private $mutatorName;
    private $mutatedNodeClass;
    private $mutatedNode;
    private $mutationByMutatorIndex;
    private $attributes;
    private $originalFileAst;
    private $tests;
    private $coveredByTests;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param CoverageLineData[] $tests
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
        Assert::allIsInstanceOf($tests, CoverageLineData::class);

        foreach (self::ATTRIBUTE_KEYS as $key) {
            Assert::keyExists($attributes, $key);
        }

        $this->originalFilePath = $originalFilePath;
        $this->originalFileAst = $originalFileAst;
        $this->mutatorName = $mutatorName;
        $this->attributes = array_intersect_key($attributes, array_flip(self::ATTRIBUTE_KEYS));
        $this->mutatedNodeClass = $mutatedNodeClass;
        $this->mutatedNode = $mutatedNode;
        $this->mutationByMutatorIndex = $mutationByMutatorIndex;
        $this->tests = $tests;
        $this->coveredByTests = count($tests) > 0;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    /**
     * return Node[]
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

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }

    public function getMutatedNode(): MutatedNode
    {
        return $this->mutatedNode;
    }

    public function isCoveredByTest(): bool
    {
        return $this->coveredByTests;
    }

    /**
     * @return CoverageLineData[]
     */
    public function getAllTests(): array
    {
        return $this->tests;
    }

    public function getHash(): string
    {
        if ($this->hash === null) {
            $this->hash = $this->createHash();
        }

        return $this->hash;
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
