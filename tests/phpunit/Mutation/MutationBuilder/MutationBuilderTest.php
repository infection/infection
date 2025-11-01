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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutator\Loop\For_;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutation\MutationAssertions;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationBuilder::class)]
#[CoversClass(MutationBuilderScenario::class)]
final class MutationBuilderTest extends TestCase
{
    use MutationAssertions;

    #[DataProvider('mutationProvider')]
    public function test_it_can_create_a_mutation(MutationBuilderScenario $scenario): void
    {
        $mutation = $scenario->builder->build();

        $this->assertMutationStateIs(
            $mutation,
            $scenario->expectedOriginalFilePath,
            $scenario->expectedOriginalFileAst,
            $scenario->expectedMutatorClass,
            $scenario->expectedMutatorName,
            $scenario->expectedAttributes,
            $scenario->expectedMutatedNodeClass,
            $scenario->expectedTests,
            $scenario->expectedOriginalFileTokens,
            $scenario->expectedOriginalFileContent,
            $scenario->expectedIsCoveredByTest,
        );
    }

    public static function mutationProvider(): iterable
    {
        yield 'minimal mutation' => [
            MutationBuilderScenario::create(
                builder: MutationBuilder::withMinimalTestData(),
                expectedOriginalFilePath: 'src/Foo.php',
                expectedOriginalFileAst: [],
                expectedMutatorClass: For_::class,
                expectedMutatorName: MutatorName::getName(For_::class),
                expectedAttributes: [
                    'startLine' => 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                expectedMutatedNodeClass: 'Unknown',
                expectedTests: [],
                expectedOriginalFileTokens: [],
                expectedOriginalFileContent: '<?php',
                expectedIsCoveredByTest: false,
            ),
        ];

        yield 'complete mutation' => [
            MutationBuilderScenario::create(
                builder: MutationBuilder::withCompleteTestData(),
                expectedOriginalFilePath: '/path/to/src/Foo.php',
                expectedOriginalFileAst: [new Nop()],
                expectedMutatorClass: For_::class,
                expectedMutatorName: MutatorName::getName(For_::class),
                expectedAttributes: [
                    'startLine' => 10,
                    'endLine' => 15,
                    'startTokenPos' => 0,
                    'endTokenPos' => 8,
                    'startFilePos' => 2,
                    'endFilePos' => 4,
                ],
                expectedMutatedNodeClass: 'PhpParser\\Node\\Stmt\\Nop',
                expectedTests: [
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
                expectedOriginalFileTokens: [],
                expectedOriginalFileContent: <<<'PHP'
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
                expectedIsCoveredByTest: true,
            ),
        ];
    }

    #[DataProvider('mutationProvider')]
    public function test_it_can_build_from_existing_mutation(MutationBuilderScenario $scenario): void
    {
        $expected = $scenario->builder->build();

        $actual = MutationBuilder::from($expected)->build();

        $this->assertMutationEquals($expected, $actual);
    }
}
