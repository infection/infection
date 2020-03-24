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

use function array_merge;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use function md5;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class MutationTest extends TestCase
{
    /**
     * @dataProvider valuesProvider
     *
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param array<string|int|float> $expectedAttributes
     * @param TestLocation[] $tests
     */
    public function test_it_can_be_instantiated(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorName,
        array $attributes,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        int $mutationByMutatorIndex,
        array $tests,
        array $expectedAttributes,
        int $expectedOriginalStartingLine,
        bool $expectedHasTests,
        string $expectedHash
    ): void {
        $mutation = new Mutation(
            $originalFilePath,
            $originalFileAst,
            $mutatorName,
            $attributes,
            $mutatedNodeClass,
            $mutatedNode,
            $mutationByMutatorIndex,
            $tests
        );

        $this->assertSame($originalFilePath, $mutation->getOriginalFilePath());
        $this->assertSame($originalFileAst, $mutation->getOriginalFileAst());
        $this->assertSame($mutatorName, $mutation->getMutatorName());
        $this->assertSame($expectedAttributes, $mutation->getAttributes());
        $this->assertSame($expectedOriginalStartingLine, $mutation->getOriginalStartingLine());
        $this->assertSame($mutatedNodeClass, $mutation->getMutatedNodeClass());
        $this->assertSame($mutatedNode, $mutation->getMutatedNode());
        $this->assertSame($tests, $mutation->getTests());
        $this->assertSame($expectedHasTests, $mutation->hasTests());
        $this->assertSame($expectedHash, $mutation->getHash());
    }

    public function valuesProvider(): iterable
    {
        $nominalAttributes = [
            'startLine' => $originalStartingLine = 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        yield 'empty' => [
            '',
            [],
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            -1,
            [],
            $nominalAttributes,
            $originalStartingLine,
            false,
            md5('_Plus_-1_3_5_21_31_43_53'),
        ];

        yield 'nominal with a test' => [
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            0,
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            $nominalAttributes,
            $originalStartingLine,
            true,
            md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53'),
        ];

        yield 'nominal with a test with a different mutator index' => [
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            99,
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            $nominalAttributes,
            $originalStartingLine,
            true,
            md5('/path/to/acme/Foo.php_Plus_99_3_5_21_31_43_53'),
        ];

        yield 'nominal with a test and additional attributes' => [
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            MutatorName::getName(Plus::class),
            array_merge($nominalAttributes, ['foo' => 100, 'bar' => 1000]),
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            0,
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            $nominalAttributes,
            $originalStartingLine,
            true,
            md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53'),
        ];

        yield 'nominal without a test' => [
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            0,
            [],
            $nominalAttributes,
            $originalStartingLine,
            false,
            md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53'),
        ];

        yield 'nominal with a test and multiple mutated nodes' => [
            '/path/to/acme/Foo.php',
            [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            Node\Scalar\LNumber::class,
            MutatedNode::wrap([
                new Node\Scalar\LNumber(1),
                new Node\Scalar\LNumber(-1),
            ]),
            0,
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            $nominalAttributes,
            $originalStartingLine,
            true,
            md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53'),
        ];
    }
}
