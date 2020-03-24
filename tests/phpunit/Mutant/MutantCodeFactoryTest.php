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

namespace Infection\Tests\Mutant;

use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use Infection\Tests\SingletonContainer;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class MutantCodeFactoryTest extends TestCase
{
    /**
     * @var \Infection\Mutation\MutantCodeFactory
     */
    private $codeFactory;

    protected function setUp(): void
    {
        $this->codeFactory = SingletonContainer::getContainer()->getMutantCodeFactory();
    }

    /**
     * @dataProvider mutationProvider
     *
     * @param array<string|int|float> $attributes
     * @param Node[] $originalFileAst
     * @param class-string $mutatedNodeClass
     */
    public function test_it_creates_the_mutant_code_from_the_given_mutation(
        array $attributes,
        array $originalFileAst,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        string $expectedMutantCode
    ): void {
        $mutationCode = $this->codeFactory->createCode(
            $attributes,
            $originalFileAst,
            $mutatedNodeClass,
            $mutatedNode
        );

        $this->assertSame($expectedMutantCode, $mutationCode);
    }

    /**
     * @dataProvider mutationProvider
     *
     * @param array<string|int|float> $attributes
     * @param Node[] $originalFileAst
     * @param class-string $mutatedNodeClass
     */
    public function test_it_creates_the_mutant_code_without_altering_the_original_nodes(
        array $attributes,
        array $originalFileAst,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode
    ): void {
        $originalNodesDump = SingletonContainer::getNodeDumper()->dump($originalFileAst);

        $this->codeFactory->createCode(
            $attributes,
            $originalFileAst,
            $mutatedNodeClass,
            $mutatedNode
        );

        $originalNodesDumpAfterMutation = SingletonContainer::getNodeDumper()->dump($originalFileAst);

        $this->assertSame($originalNodesDump, $originalNodesDumpAfterMutation);
    }

    public function mutationProvider(): iterable
    {
        yield [
            [
                'startLine' => 5,
                'startTokenPos' => 9,
                'startFilePos' => 29,
                'endLine' => 5,
                'endTokenPos' => 9,
                'endFilePos' => 30,
                'kind' => 10,
            ],
            [new Node\Stmt\Namespace_(
                new Node\Name(
                    'Acme',
                    [
                        'startLine' => 3,
                        'startTokenPos' => 4,
                        'startFilePos' => 17,
                        'endLine' => 3,
                        'endTokenPos' => 4,
                        'endFilePos' => 20,
                    ]
                ),
                [new Node\Stmt\Echo_(
                    [new Node\Scalar\LNumber(
                        10,
                        [
                            'startLine' => 5,
                            'startTokenPos' => 9,
                            'startFilePos' => 29,
                            'endLine' => 5,
                            'endTokenPos' => 9,
                            'endFilePos' => 30,
                            'kind' => 10,
                        ]
                    )],
                    [
                        'startLine' => 5,
                        'startTokenPos' => 7,
                        'startFilePos' => 24,
                        'endLine' => 5,
                        'endTokenPos' => 10,
                        'endFilePos' => 31,
                    ]
                )],
                [
                    'startLine' => 3,
                    'startTokenPos' => 2,
                    'startFilePos' => 7,
                    'endLine' => 5,
                    'endTokenPos' => 10,
                    'endFilePos' => 31,
                    'kind' => 1,
                ]
            )],
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(
                new Node\Scalar\LNumber(
                    15,
                    [
                        'startLine' => 5,
                        'startTokenPos' => 9,
                        'startFilePos' => 29,
                        'endLine' => 5,
                        'endTokenPos' => 9,
                        'endFilePos' => 30,
                        'kind' => 10,
                    ]
                )
            ),
            <<<'PHP'
<?php

namespace Acme;

echo 15;
PHP
        ];
    }
}
