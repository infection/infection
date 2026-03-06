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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use Infection\Logger\MutationAnalysis\TeamCity\Test;
use Infection\Mutation\Mutation;
use Infection\Mutator\Boolean\LogicalOr as LogicalOrMutator;
use Infection\Testing\MutatorName;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Test::class)]
final class TestTest extends TestCase
{
    #[DataProvider('caseProvider')]
    public function test_it_can_be_created(
        Mutation $mutation,
        string $parentNodeId,
        Test $expected,
    ): void {
        $actual = Test::create($mutation, $parentNodeId);

        $this->assertEquals($expected, $actual);
    }

    // We cannot use "testProvider" here, PHPUnit would otherwise understand it as a test.
    public static function caseProvider(): iterable
    {
        yield [
            self::createMutation(
                '/path/to/project/src/Infrastructure/Http/Action/Greet.php',
                LogicalOrMutator::class,
            ),
            'a93f8006e20d02d1',
            new Test(
                'fb9282c1c4fec68667212fb805238bc9',
                'Infection\Mutator\Boolean\LogicalOr (fb9282c1c4fec68667212fb805238bc9)',
                '2b4aa030e4a6eead',
                'a93f8006e20d02d1',
            ),
        ];
    }

    private static function createMutation(
        string $sourceFilePath,
        string $mutatorClassName,
    ): Mutation {
        return MutationBuilder::withMinimalTestData()
            ->withOriginalFilePath($sourceFilePath)
            ->withMutatorClass($mutatorClassName)
            ->withMutatorName(MutatorName::getName($mutatorClassName))
            ->build();
    }
}
