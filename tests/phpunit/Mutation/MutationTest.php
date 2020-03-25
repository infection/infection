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
use Infection\Mutation\MutationCalculatedState;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Tests\Mutator\MutatorName;
use PHPUnit\Framework\TestCase;

final class MutationTest extends TestCase
{
    /**
     * @dataProvider valuesProvider
     *
     * @param array<string|int|float> $attributes
     * @param TestLocation[] $tests
     */
    public function test_it_can_be_instantiated(
        string $originalFilePath,
        string $mutatorName,
        array $attributes,
        array $tests,
        string $hash,
        string $filePath,
        string $code,
        string $diff,
        int $expectedOriginalStartingLine,
        bool $expectedHasTests
    ): void {
        $mutation = new Mutation(
            $originalFilePath,
            $mutatorName,
            $attributes,
            $tests,
            static function () use ($hash, $filePath, $code, $diff): MutationCalculatedState {
                return new MutationCalculatedState(
                    $hash,
                    $filePath,
                    $code,
                    $diff
                );
            }
        );

        $this->assertSame($originalFilePath, $mutation->getOriginalFilePath());
        $this->assertSame($mutatorName, $mutation->getMutatorName());
        $this->assertSame($expectedOriginalStartingLine, $mutation->getOriginalStartingLine());
        $this->assertSame($tests, $mutation->getTests());
        $this->assertSame($expectedHasTests, $mutation->hasTests());
        $this->assertSame($hash, $mutation->getHash());
        $this->assertSame($filePath, $mutation->getFilePath());
        $this->assertSame($code, $mutation->getMutatedCode());
        $this->assertSame($diff, $mutation->getDiff());
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
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            [],
            '',
            '',
            '',
            '',
            $originalStartingLine,
            false,
        ];

        yield 'nominal with a test' => [
            '/path/to/acme/Foo.php',
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            '0800f',
            '/path/to/mutation',
            'notCovered#0',
            <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
            ,
            $originalStartingLine,
            true,
        ];

        yield 'nominal with a test and additional attributes' => [
            '/path/to/acme/Foo.php',
            MutatorName::getName(Plus::class),
            array_merge($nominalAttributes, ['foo' => 100, 'bar' => 1000]),
            [
                new TestLocation(
                    'FooTest::test_it_can_instantiate',
                    '/path/to/acme/FooTest.php',
                    0.01
                ),
            ],
            '0800f',
            '/path/to/mutation',
            'notCovered#0',
            <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
            ,
            $originalStartingLine,
            true,
        ];

        yield 'nominal without a test' => [
            '/path/to/acme/Foo.php',
            MutatorName::getName(Plus::class),
            $nominalAttributes,
            [],
            '0800f',
            '/path/to/mutation',
            'notCovered#0',
            <<<'DIFF'
--- Original
+++ New
@@ @@

- echo 'original';
+ echo 'notCovered#0';

DIFF
            ,
            $originalStartingLine,
            false,
        ];
    }
}
