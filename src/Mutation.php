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

namespace Infection;

use function count;
use Infection\TestFramework\Coverage\CoverageLineData;
use PhpParser\Node;

/**
 * @internal
 * @final
 */
class Mutation
{
    private $originalFilePath;
    private $mutatorClass;
    private $mutatedNodeClass;
    private $mutatedNode;
    private $mutationByMutatorIndex;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var Node[]
     */
    private $originalFileAst;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var CoverageLineData[]
     */
    private $tests;

    /**
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param Node|Node[] $mutatedNode
     */
    public function __construct(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorClass,
        array $attributes,
        string $mutatedNodeClass,
        $mutatedNode,
        int $mutationByMutatorIndex,
        array $tests
    ) {
        $this->originalFilePath = $originalFilePath;
        $this->originalFileAst = $originalFileAst;
        $this->mutatorClass = $mutatorClass;
        $this->attributes = $attributes;
        $this->mutatedNodeClass = $mutatedNodeClass;
        $this->mutatedNode = $mutatedNode;
        $this->mutationByMutatorIndex = $mutationByMutatorIndex;
        $this->tests = $tests;
    }

    public function getMutatorClass(): string
    {
        return $this->mutatorClass;
    }

    /**
     * @return (string|int|float)[]   $attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getOriginalFilePath(): string
    {
        return $this->originalFilePath;
    }

    public function getMutatedNodeClass(): string
    {
        return $this->mutatedNodeClass;
    }

    public function getHash(): string
    {
        if (!isset($this->hash)) {
            $attributes = $this->getAttributes();

            $attributeValues = [
                $this->mutatorClass,
                $attributes['startLine'],
                $attributes['endLine'],
                $attributes['startTokenPos'],
                $attributes['endTokenPos'],
                $attributes['startFilePos'],
                $attributes['endFilePos'],
            ];

            $hashKeys = array_merge(
                [$this->getOriginalFilePath(), $this->mutatorClass, $this->mutationByMutatorIndex],
                $attributeValues
            );

            $this->hash = md5(implode('_', $hashKeys));
        }

        return $this->hash;
    }

    /**
     * return Node[]
     */
    public function getOriginalFileAst(): array
    {
        return $this->originalFileAst;
    }

    /**
     * @return CoverageLineData[]
     */
    public function getAllTests(): array
    {
        return $this->tests;
    }

    public function isCoveredByTest(): bool
    {
        return count($this->getAllTests()) !== 0;
    }

    /**
     * @return Node|Node[]
     */
    public function getMutatedNode()
    {
        return $this->mutatedNode;
    }
}
