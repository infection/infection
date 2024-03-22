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

namespace Infection\Tests\Mutator\Removal;

use Infection\Tests\Mutator\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConcatOperandRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->doTest($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'Removes both operands' => [
            <<<'PHP'
                <?php
                'foo' . 'bar';
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    'bar';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    'foo';
                    PHP
                ,
            ],
        ];

        yield 'Removes each part of a 3-string concatenation' => [
            <<<'PHP'
                <?php

                $a = 'a';
                $b = 'b';
                $a . $b . 'c';
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $b . 'c';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $a . 'c';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $a . $b;
                    PHP
                ,
            ],
        ];

        yield 'Removes each part of multiple concatenations' => [
            <<<'PHP'
                <?php

                $a = 'a';
                $b = 'b';
                $d = 'd';
                $a . $b . 'c' . $d . 'e';
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $d = 'd';
                    $b . 'c' . $d . 'e';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $d = 'd';
                    $a . 'c' . $d . 'e';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $d = 'd';
                    $a . $b . $d . 'e';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $d = 'd';
                    $a . $b . 'c' . 'e';
                    PHP
                ,
                <<<'PHP'
                    <?php

                    $a = 'a';
                    $b = 'b';
                    $d = 'd';
                    $a . $b . 'c' . $d;
                    PHP
                ,
            ],
        ];
    }
}
