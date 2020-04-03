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
use Infection\Differ\Differ;
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutation\MutationFactory;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use function md5;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;

final class MutationFactoryTest extends TestCase
{
    use MutationAssertions;

    /**
     * @var MutantCodeFactory|MockObject
     */
    private $codeFactoryMock;

    /**
     * @var PrettyPrinterAbstract|MockObject
     */
    private $printerMock;

    /**
     * @var Differ|MockObject
     */
    private $differMock;

    /**
     * @var MutationFactory
     */
    private $mutationFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeFactoryMock = $this->createMock(MutantCodeFactory::class);
        $this->printerMock = $this->createMock(PrettyPrinterAbstract::class);
        $this->differMock = $this->createMock(Differ::class);

        $this->mutationFactory = new MutationFactory(
            '/path/to/tmp',
            $this->differMock,
            $this->printerMock,
            $this->codeFactoryMock
        );
    }

    /**
     * @dataProvider valuesProvider
     *
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param class-string $mutatedNodeClass
     * @param TestLocation[] $tests
     * @param array<string|int|float> $expectedFilteredAttributes
     */
    public function test_it_creates_a_mutation(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorName,
        array $attributes,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        int $mutationByMutatorIndex,
        array $tests,
        array $expectedFilteredAttributes,
        string $expectedHash,
        string $expectedMutationFilePath,
        int $expectedOriginalStartingLine
    ): void {
        $this->codeFactoryMock
            ->expects($this->exactly(2))
            ->method('createCode')
            ->with(
                $expectedFilteredAttributes,
                $originalFileAst,
                $mutatedNodeClass,
                $mutatedNode
            )
            ->willReturn('mutated code')
        ;

        $this->printerMock
            ->expects($this->once())
            ->method('prettyPrintFile')
            ->with($originalFileAst)
            ->willReturn('original code')
        ;

        $this->differMock
            ->expects($this->exactly(2))
            ->method('diff')
            ->with('original code', 'mutated code')
            ->willReturn('code diff')
        ;

        $mutation1 = $this->mutationFactory->create(
            $originalFilePath,
            $originalFileAst,
            $mutatorName,
            $attributes,
            $mutatedNodeClass,
            $mutatedNode,
            $mutationByMutatorIndex,
            $tests
        );

        $this->assertMutationSateIs(
            $mutation1,
            $originalFilePath,
            $mutatorName,
            $tests,
            $expectedHash,
            $expectedMutationFilePath,
            'mutated code',
            'code diff',
            $expectedOriginalStartingLine,
            true
        );

        // Check memoization

        $mutation2 = $this->mutationFactory->create(
            $originalFilePath,
            $originalFileAst,
            $mutatorName,
            $attributes,
            $mutatedNodeClass,
            $mutatedNode,
            $mutationByMutatorIndex,
            $tests
        );

        $this->assertMutationSateIs(
            $mutation2,
            $originalFilePath,
            $mutatorName,
            $tests,
            $expectedHash,
            $expectedMutationFilePath,
            'mutated code',
            'code diff',
            $expectedOriginalStartingLine,
            true
        );
    }

    public static function valuesProvider(): iterable
    {
        $nominalAttributes = [
            'startLine' => $originalStartingLine = 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        yield 'nominal' => (static function () use (
            $nominalAttributes,
            $originalStartingLine
        ): array {
            $expectedHash = md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53');

            return [
                '/path/to/acme/Foo.php',
                [
                    new Node\Stmt\Namespace_(
                        new Node\Name('Acme'),
                        [new Node\Scalar\LNumber(0)]
                    ),
                ],
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
                $expectedHash,
                sprintf(
                    '/path/to/tmp/mutation.%s.infection.php',
                    $expectedHash
                ),
                $originalStartingLine,
            ];
        })();

        yield 'with additional attributes' => (static function () use (
            $nominalAttributes,
            $originalStartingLine
        ): array {
            $expectedHash = md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53');

            return [
                '/path/to/acme/Foo.php',
                [
                    new Node\Stmt\Namespace_(
                        new Node\Name('Acme'),
                        [new Node\Scalar\LNumber(0)]
                    ),
                ],
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
                $expectedHash,
                sprintf(
                    '/path/to/tmp/mutation.%s.infection.php',
                    $expectedHash
                ),
                $originalStartingLine,
            ];
        })();
    }
}
