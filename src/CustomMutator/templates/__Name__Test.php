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

namespace App\Tests\Mutator;

use App\Mutator\__Name__;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(__Name__::class)]
final class __Name__Test extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        // TODO: write original and mutated code in each cases
        // TODO: if you want to test that the code is not mutated, just leave one element (source code) in the array
        // TODO: if mutator produces N mutants, the second array item must be an array of mutated codes

        yield 'It mutates a simple case' => [
            <<<'PHP'
                <?php

                $a = 10 + 3;
                PHP
            ,
            <<<'PHP'
                <?php

                $a = 10 - 3;
                PHP
            ,
        ];

        //        yield 'It does not mutate other cases' => [
        //            <<<'PHP'
        //                <?php
        //
        //                $a = 10 * 3;
        //                PHP
        //            ,
        //        ];

        //        yield 'It produces N mutations' => [
        //            <<<'PHP'
        //                <?php
        //
        //                $a = 10 - 3;
        //                PHP
        //            ,
        //            [
        //                <<<'PHP'
        //                <?php
        //
        //                $a = 10 - 3;
        //                PHP,
        //                <<<'PHP'
        //                <?php
        //
        //                $a = 10 - -3;
        //                PHP
        //            ]
        //        ];
    }

    protected function getTestedMutatorClassName(): string
    {
        return __Name__::class;
    }
}
