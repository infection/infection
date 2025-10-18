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

use Infection\Mutant\MutantCodeFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Testing\MutatorName;
use Infection\Testing\SingletonContainer;
use PhpParser\Node;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[CoversClass(MutantCodeFactory::class)]
final class MutantCodeFactoryTest extends TestCase
{
    private const PHP_TO_BE_MUTATED_CODE = <<<'PHP_WRAP'
        <?php

        $a = PHP_INT_MAX - 33;
        PHP_WRAP;

    private const PHP_UNTOUCHED_CODE = <<<'PHP_WRAP'
        <?php

        namespace PHPStan_Integration;

        class SourceClass
        {
            /**
             * @template T
             * @param array<T> $values
             * @return list<T>
             */
            public function makeAList(array $values): array
            {
                // some code to generate more mutations

                $strings = [
                '1'];

                $ints = array_map(function ($value): int {
                    return (int) $value;
                }, $strings);

                $nonEmptyArray = ['1'];

                $nonEmptyArrayFromMethod = $this->returnNonEmptyArray();

                $inlineNonEmpty = ['1'];

                return array_values($values);
            }

            /**
             * @return non-empty-array<int, string>
             */
            private function returnNonEmptyArray(): array
            {
                return ['test'];
            }
        }

        PHP_WRAP;

    private MutantCodeFactory $codeFactory;

    protected function setUp(): void
    {
        $this->codeFactory = SingletonContainer::getContainer()->getMutantCodeFactory();
    }

    #[DataProvider('mutationProvider')]
    public function test_it_creates_the_mutant_code_from_the_given_mutation(
        Mutation $mutation,
        string $expectedMutantCode,
    ): void {
        $mutantCode = $this->codeFactory->createCode($mutation);

        $this->assertSame($expectedMutantCode, $mutantCode);
    }

    #[DataProvider('mutationProvider')]
    public function test_it_creates_the_mutant_code_without_altering_the_original_nodes(
        Mutation $mutation,
    ): void {
        $originalNodesDump = SingletonContainer::getNodeDumper()->dump($mutation->getOriginalFileAst());

        $this->codeFactory->createCode($mutation);

        $originalNodesDumpAfterMutation = SingletonContainer::getNodeDumper()->dump($mutation->getOriginalFileAst());

        $this->assertSame($originalNodesDump, $originalNodesDumpAfterMutation);
    }

    public static function mutationProvider(): iterable
    {
        $parser = (new ParserFactory())->createForHostVersion();

        $originalStmts = $parser->parse(self::PHP_UNTOUCHED_CODE);
        $originalTokens = $parser->getTokens();

        Assert::notNull($originalStmts);

        yield 'keeps pretty-printing' => [
            new Mutation(
                '/path/to/acme/Foo.php',
                $originalStmts,
                Plus::class,
                MutatorName::getName(Plus::class),
                [
                    'startLine' => 5,
                    'startTokenPos' => 9,
                    'startFilePos' => 29,
                    'endLine' => 5,
                    'endTokenPos' => 9,
                    'endFilePos' => 30,
                    'kind' => 10,
                ],
                Node\Scalar\Int_::class,
                MutatedNode::wrap(
                    new Node\Scalar\Int_(
                        15,
                        [
                            'startLine' => 5,
                            'startTokenPos' => 9,
                            'startFilePos' => 29,
                            'endLine' => 5,
                            'endTokenPos' => 9,
                            'endFilePos' => 30,
                            'kind' => 10,
                        ],
                    ),
                ),
                0,
                [],
                $originalTokens,
                self::PHP_UNTOUCHED_CODE,
            ),
            self::PHP_UNTOUCHED_CODE,
        ];

        $originalStmts = $parser->parse(self::PHP_TO_BE_MUTATED_CODE);
        $originalTokens = $parser->getTokens();

        Assert::notNull($originalStmts);

        yield 'mutates + to -' => [
            new Mutation(
                '/path/to/acme/Foo.php',
                $originalStmts,
                Plus::class,
                MutatorName::getName(Plus::class),
                [
                    'startLine' => 3,
                    'startTokenPos' => 10,
                    'startFilePos' => 26,
                    'endLine' => 3,
                    'endTokenPos' => 10,
                    'endFilePos' => 27,
                    'rawValue' => '33',
                    'kind' => 10,
                ],
                Node\Scalar\Int_::class,
                MutatedNode::wrap(
                    new Node\Scalar\Int_(
                        32,
                        [
                            'startLine' => 3,
                            'startTokenPos' => 10,
                            'startFilePos' => 26,
                            'endLine' => 3,
                            'endTokenPos' => 10,
                            'endFilePos' => 27,
                            'rawValue' => '32',
                            'kind' => 10,
                        ],
                    ),
                ),
                0,
                [],
                $originalTokens,
                self::PHP_TO_BE_MUTATED_CODE,
            ),
            <<<'PHP_WRAP'
                <?php

                $a = PHP_INT_MAX - 32;
                PHP_WRAP,
        ];
    }
}
