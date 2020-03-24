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

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Differ\Differ;
use Infection\Mutation\MutantCodeFactory;
use Infection\Mutation\Mutation;
use Infection\Mutation\MutationFactory;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutation\MutationAssertions;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function md5;
use function Safe\sprintf;

final class MutantFactoryTest extends TestCase
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
     * @var \Infection\Mutation\MutationFactory
     */
    private $mutantFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeFactoryMock = $this->createMock(MutantCodeFactory::class);

        $this->printerMock = $this->createMock(PrettyPrinterAbstract::class);

        $this->differMock = $this->createMock(Differ::class);

        $this->mutantFactory = new MutationFactory(
            '/path/to/tmp',
            $this->differMock,
            $this->printerMock,
            $this->codeFactoryMock
        );
    }

    public function test_it_creates_a_mutation(): void
    {
        $originalFilePath = '/path/to/acme/Foo.php';
        $originalFileAst = [new Node\Stmt\Namespace_(
            new Node\Name('Acme'),
            [new Node\Scalar\LNumber(0)]
        )];
        $mutatorName = MutatorName::getName(Plus::class);
        $attributes = [
            'startLine' => $originalStartingLine = 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];
        $mutatedNodeClass = Node\Scalar\LNumber::class;
        $mutatedNode = MutatedNode::wrap(new Node\Scalar\LNumber(1));
        $mutationByMutatorIndex = 0;
        $tests = [
            new TestLocation(
                'FooTest::test_it_can_instantiate',
                '/path/to/acme/FooTest.php',
                0.01
            ),
        ];

        $expectedHash = md5('/path/to/acme/Foo.php_Plus_0_3_5_21_31_43_53');

        $expectedMutantFilePath = sprintf(
            '/path/to/tmp/mutant.%s.infection.php',
            $expectedHash
        );

        $this->codeFactoryMock
            ->expects($this->exactly(2))
            ->method('createCode')
            ->with(
                $attributes,
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

        $mutation1 = $this->mutantFactory->create(
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
            $expectedMutantFilePath,
            'mutated code',
            'code diff',
            $originalStartingLine,
            true
        );

        // Check memoization

        $mutation2 = $this->mutantFactory->create(
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
            $expectedMutantFilePath,
            'mutated code',
            'code diff',
            $originalStartingLine,
            true
        );
    }
}
