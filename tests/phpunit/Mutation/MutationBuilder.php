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
use Infection\Mutator\Loop\For_;
use Infection\PhpParser\MutatedNode;
use Infection\Testing\MutatorName;
use PhpParser\Node;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Token;

final class MutationBuilder
{
    /**
     * @param Node[] $originalFileAst
     * @param array<string, string|int|float> $attributes
     * @param TestLocation[] $tests
     * @param Token[] $originalFileTokens
     */
    private function __construct(
        private string $originalFilePath,
        private array $originalFileAst,
        private string $mutatorClass,
        private string $mutatorName,
        private array $attributes,
        private string $mutatedNodeClass,
        private MutatedNode $mutatedNode,
        private int $mutationByMutatorIndex,
        private array $tests,
        private array $originalFileTokens,
        private string $originalFileContent,
    ) {
    }

    public static function from(Mutation $mutation): self
    {
        return new self(
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalFileAst(),
            $mutation->getMutatorClass(),
            $mutation->getMutatorName(),
            $mutation->getAttributes(),
            $mutation->getMutatedNodeClass(),
            $mutation->getMutatedNode(),
            0, // mutationByMutatorIndex is not exposed via getter
            $mutation->getAllTests(),
            $mutation->getOriginalFileTokens(),
            $mutation->getOriginalFileContent(),
        );
    }

    public static function withMinimalTestData(): self
    {
        return new self(
            originalFilePath: 'src/Foo.php',
            originalFileAst: [],
            mutatorClass: For_::class,
            mutatorName: MutatorName::getName(For_::class),
            attributes: [
                'startLine' => 10,
                'endLine' => 15,
                'startTokenPos' => 0,
                'endTokenPos' => 8,
                'startFilePos' => 2,
                'endFilePos' => 4,
            ],
            mutatedNodeClass: 'Unknown',
            mutatedNode: MutatedNode::wrap(new Nop()),
            mutationByMutatorIndex: 0,
            tests: [],
            originalFileTokens: [],
            originalFileContent: '<?php',
        );
    }

    public static function withCompleteTestData(): self
    {
        return new self(
            originalFilePath: '/path/to/src/Foo.php',
            originalFileAst: [new Nop()],
            mutatorClass: For_::class,
            mutatorName: MutatorName::getName(For_::class),
            attributes: [
                'startLine' => 10,
                'endLine' => 15,
                'startTokenPos' => 0,
                'endTokenPos' => 8,
                'startFilePos' => 2,
                'endFilePos' => 4,
            ],
            mutatedNodeClass: Nop::class,
            mutatedNode: MutatedNode::wrap(new Nop()),
            mutationByMutatorIndex: 3,
            tests: [
                new TestLocation(
                    'FooTest::test_it_can_do_something',
                    '/path/to/tests/FooTest.php',
                    0.123,
                ),
                new TestLocation(
                    'FooTest::test_it_can_do_something_else',
                    '/path/to/tests/FooTest.php',
                    0.456,
                ),
            ],
            originalFileTokens: [],
            originalFileContent: <<<'PHP'
                <?php

                namespace Acme;

                class Foo
                {
                    public function bar(): void
                    {
                        for ($i = 0; $i < 10; $i++) {
                            echo $i;
                        }
                    }
                }

                PHP,
        );
    }

    public function withOriginalFilePath(string $originalFilePath): self
    {
        $clone = clone $this;
        $clone->originalFilePath = $originalFilePath;

        return $clone;
    }

    /**
     * @param Node[] $originalFileAst
     */
    public function withOriginalFileAst(array $originalFileAst): self
    {
        $clone = clone $this;
        $clone->originalFileAst = $originalFileAst;

        return $clone;
    }

    public function withMutatorClass(string $mutatorClass): self
    {
        $clone = clone $this;
        $clone->mutatorClass = $mutatorClass;

        return $clone;
    }

    public function withMutatorName(string $mutatorName): self
    {
        $clone = clone $this;
        $clone->mutatorName = $mutatorName;

        return $clone;
    }

    /**
     * @param array<string, string|int|float> $attributes
     */
    public function withAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->attributes = $attributes;

        return $clone;
    }

    public function withMutatedNodeClass(string $mutatedNodeClass): self
    {
        $clone = clone $this;
        $clone->mutatedNodeClass = $mutatedNodeClass;

        return $clone;
    }

    public function withMutatedNode(MutatedNode $mutatedNode): self
    {
        $clone = clone $this;
        $clone->mutatedNode = $mutatedNode;

        return $clone;
    }

    public function withMutationByMutatorIndex(int $mutationByMutatorIndex): self
    {
        $clone = clone $this;
        $clone->mutationByMutatorIndex = $mutationByMutatorIndex;

        return $clone;
    }

    /**
     * @param TestLocation[] $tests
     */
    public function withTests(array $tests): self
    {
        $clone = clone $this;
        $clone->tests = $tests;

        return $clone;
    }

    /**
     * @param Token[] $originalFileTokens
     */
    public function withOriginalFileTokens(array $originalFileTokens): self
    {
        $clone = clone $this;
        $clone->originalFileTokens = $originalFileTokens;

        return $clone;
    }

    public function withOriginalFileContent(string $originalFileContent): self
    {
        $clone = clone $this;
        $clone->originalFileContent = $originalFileContent;

        return $clone;
    }

    public function build(): Mutation
    {
        return new Mutation(
            $this->originalFilePath,
            $this->originalFileAst,
            $this->mutatorClass,
            $this->mutatorName,
            $this->attributes,
            $this->mutatedNodeClass,
            $this->mutatedNode,
            $this->mutationByMutatorIndex,
            $this->tests,
            $this->originalFileTokens,
            $this->originalFileContent,
        );
    }
}
