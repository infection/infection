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

use Infection\Mutation\Mutation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationAssertions::class)]
final class MutationAssertionsTest extends TestCase
{
    #[DataProvider('mutationProvider')]
    public function test_it_can_compare_mutations(
        Mutation $left,
        Mutation $right,
        bool $expected,
    ): void {
        try {
            MutationAssertions::assertEquals($left, $right);

            if (!$expected) {
                $this->fail('Expected mutations to not be equal.');
            }
        } catch (ExpectationFailedException $failure) {
            // @phpstan-ignore if.alwaysFalse
            if ($expected) {
                throw $failure;
            }
        }
    }

    public static function mutationProvider(): iterable
    {
        yield 'equal' => [
            MutationBuilder::withMinimalTestData()->build(),
            MutationBuilder::withMinimalTestData()->build(),
            true,
        ];

        yield 'not equal' => [
            MutationBuilder::withMinimalTestData()->build(),
            MutationBuilder::withCompleteTestData()->build(),
            false,
        ];

        yield 'equal but different states' => [
            (static function () {
                $mutation = MutationBuilder::withCompleteTestData()->build();

                self::fetchLazyState($mutation);

                return $mutation;
            })(),
            MutationBuilder::withCompleteTestData()->build(),
            true,
        ];
    }

    private static function fetchLazyState(Mutation $mutation): void
    {
        $mutation->getNominalTestExecutionTime();
        $mutation->getHash();
    }
}
