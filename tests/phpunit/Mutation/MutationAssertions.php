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

namespace Infection\Tests\Mutation;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use PhpParser\Node;
use PhpParser\Token;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-require-implements TestCase
 */
trait MutationAssertions
{
    public function assertMutationEquals(
        Mutation $expected,
        Mutation $actual,
    ): void {
        $this->assertSame($expected->getOriginalFilePath(), $actual->getOriginalFilePath());
        $this->assertEquals($expected->getOriginalFileAst(), $actual->getOriginalFileAst());
        $this->assertSame($expected->getMutatorClass(), $actual->getMutatorClass());
        $this->assertSame($expected->getMutatorName(), $actual->getMutatorName());
        $this->assertSame($expected->getAttributes(), $actual->getAttributes());
        $this->assertSame($expected->getMutatedNodeClass(), $actual->getMutatedNodeClass());
        $this->assertEquals($expected->getAllTests(), $actual->getAllTests());
        $this->assertEquals($expected->getOriginalFileTokens(), $actual->getOriginalFileTokens());
        $this->assertSame($expected->getOriginalFileContent(), $actual->getOriginalFileContent());
        $this->assertSame($expected->isCoveredByTest(), $actual->isCoveredByTest());
    }

    /**
     * @param Node[] $expectedOriginalFileAst
     * @param (string|int|float)[] $expectedAttributes
     * @param TestLocation[] $expectedTests
     * @param Token[] $expectedFileTokens
     */
    public function assertMutationStateIs(
        Mutation $mutation,
        string $expectedOriginalFilePath,
        array $expectedOriginalFileAst,
        string $expectedMutatorClass,
        string $expectedMutatorName,
        array $expectedAttributes,
        string $expectedMutatedNodeClass,
        array $expectedTests,
        array $expectedFileTokens,
        string $expectedOriginalFileContent,
        bool $expectedCoveredByTests,
    ): void {
        $this->assertSame($expectedOriginalFilePath, $mutation->getOriginalFilePath());
        $this->assertEquals($expectedOriginalFileAst, $mutation->getOriginalFileAst());
        $this->assertSame($expectedMutatorClass, $mutation->getMutatorClass());
        $this->assertSame($expectedMutatorName, $mutation->getMutatorName());
        $this->assertSame($expectedAttributes, $mutation->getAttributes());
        $this->assertSame($expectedMutatedNodeClass, $mutation->getMutatedNodeClass());
        $this->assertEquals($expectedTests, $mutation->getAllTests());
        $this->assertEquals($expectedFileTokens, $mutation->getOriginalFileTokens());
        $this->assertSame($expectedOriginalFileContent, $mutation->getOriginalFileContent());
        $this->assertSame($expectedCoveredByTests, $mutation->isCoveredByTest());
    }
}
