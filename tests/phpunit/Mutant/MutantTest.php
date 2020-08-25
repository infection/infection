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
use Infection\Mutant\Mutant;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\PhpParser\MutatedNode;
use Infection\Tests\Mutator\MutatorName;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;

final class MutantTest extends TestCase
{
    use MutantAssertions;

    /**
     * @dataProvider valuesProvider
     *
     * @param TestLocation[] $expectedTests
     */
    public function test_it_can_be_instantiated(
        string $filePath,
        Mutation $mutation,
        string $mutatedCode,
        string $diff,
        bool $expectedCoveredByTests,
        array $expectedTests,
        string $originalCode
    ): void {
        $mutant = new Mutant($filePath, $mutation, $mutatedCode, $diff, $originalCode);

        $this->assertMutantStateIs(
            $mutant,
            $filePath,
            $mutation,
            $mutatedCode,
            $diff,
            $expectedCoveredByTests,
            $expectedTests,
            $originalCode
        );
    }

    public function valuesProvider(): iterable
    {
        $nominalAttributes = [
            'startLine' => 3,
            'endLine' => 5,
            'startTokenPos' => 21,
            'endTokenPos' => 31,
            'startFilePos' => 43,
            'endFilePos' => 53,
        ];

        $tests = [
            new TestLocation(
                'FooTest::test_it_can_instantiate',
                '/path/to/acme/FooTest.php',
                0.01
            ),
        ];

        $originalCode = '<?php $a = 1';

        yield 'nominal with tests' => [
            '/path/to/tmp/mutant.Foo.infection.php',
            new Mutation(
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
                $tests
            ),
            'mutated code',
            'diff value',
            true,
            $tests,
            $originalCode,
        ];

        yield 'nominal without tests' => [
            '/path/to/tmp/mutant.Foo.infection.php',
            new Mutation(
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
                []
            ),
            'mutated code',
            'diff value',
            false,
            [],
            $originalCode,
        ];
    }
}
