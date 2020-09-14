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
use Infection\Mutant\MutantCodeFactory;
use Infection\Mutant\MutantFactory;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function Safe\sprintf;

final class MutantFactoryTest extends TestCase
{
    use MutantAssertions;

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
     * @var MutantFactory
     */
    private $mutantFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->codeFactoryMock = $this->createMock(MutantCodeFactory::class);

        $this->printerMock = $this->createMock(PrettyPrinterAbstract::class);

        $this->differMock = $this->createMock(Differ::class);

        $this->mutantFactory = new MutantFactory(
            '/path/to/tmp',
            $this->differMock,
            $this->printerMock,
            $this->codeFactoryMock
        );
    }

    public function test_it_creates_a_mutant_instance_from_the_given_mutation(): void
    {
        $mutation = self::createMutation(
            $originalNodes = [new Node\Stmt\Namespace_(
                new Node\Name('Acme'),
                [new Node\Scalar\LNumber(0)]
            )],
            $tests = [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ]
        );

        $expectedMutantFilePath = sprintf(
            '/path/to/tmp/mutant.%s.infection.php',
            $mutation->getHash()
        );

        $this->codeFactoryMock
            ->expects($this->once())
            ->method('createCode')
            ->with($mutation)
            ->willReturn('mutated code')
        ;

        $originalCode = 'original code';
        $this->printerMock
            ->expects($this->once())
            ->method('prettyPrintFile')
            ->with($originalNodes)
            ->willReturn($originalCode)
        ;

        $this->differMock
            ->expects($this->once())
            ->method('diff')
            ->with('original code', 'mutated code')
            ->willReturn('code diff')
        ;

        $mutant = $this->mutantFactory->create($mutation);

        $this->assertMutantStateIs(
            $mutant,
            $expectedMutantFilePath,
            $mutation,
            'mutated code',
            'code diff',
            true,
            $tests,
            $originalCode
        );
    }

    public function test_it_printing_the_original_file_is_memoized(): void
    {
        $mutation = self::createMutation(
            $originalNodes = [new Node\Stmt\Nop()],
            []
        );

        $this->printerMock
            ->expects($this->once())
            ->method('prettyPrintFile')
            ->with($originalNodes)
            ->willReturn('original code')
        ;

        $this->differMock
            ->expects($this->atLeastOnce())
            ->method('diff')
            ->willReturn('code diff')
        ;

        $this->mutantFactory->create($mutation);
        $this->mutantFactory->create($mutation);
    }

    /**
     * @param Node[] $originalNodes
     * @param TestLocation[] $tests
     */
    private static function createMutation(array $originalNodes, array $tests): Mutation
    {
        return new Mutation(
            '/path/to/acme/Foo.php',
            $originalNodes,
            MutatorName::getName(Plus::class),
            [
                'startLine' => 3,
                'endLine' => 5,
                'startTokenPos' => 21,
                'endTokenPos' => 31,
                'startFilePos' => 43,
                'endFilePos' => 53,
            ],
            Node\Scalar\LNumber::class,
            MutatedNode::wrap(new Node\Scalar\LNumber(1)),
            0,
            $tests
        );
    }
}
