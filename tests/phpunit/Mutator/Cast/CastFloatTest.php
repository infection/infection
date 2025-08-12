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

namespace Infection\Tests\Mutator\Cast;

use Infection\Mutator\Cast\CastFloat;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CastFloat::class)]
final class CastFloatTest extends BaseMutatorTestCase
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
        yield 'It removes casting to float' => [
            <<<'PHP'
                <?php

                (float) '1.1';
                PHP
            ,
            <<<'PHP'
                <?php

                '1.1';
                PHP
            ,
        ];

        if (PHP_VERSION_ID < 80500) {
            yield 'It removes casting to double' => [
                <<<'PHP'
                <?php

                (double) '1.1';
                PHP
                ,
                <<<'PHP'
                <?php

                '1.1';
                PHP
                ,
            ];
        }

        yield 'It removes casting to real' => [
            <<<'PHP'
                <?php

                (real) '1.1';
                PHP
            ,
            <<<'PHP'
                <?php

                '1.1';
                PHP
            ,
        ];

        yield 'It removes casting to float in conditions' => [
            <<<'PHP'
                <?php

                if ((float) random_int()) {
                    echo 'Hello';
                }
                PHP
            ,
            <<<'PHP'
                <?php

                if (random_int()) {
                    echo 'Hello';
                }
                PHP
            ,
        ];

        yield 'It removes casting to float in global return' => [
            <<<'PHP'
                <?php

                return (float) random_int();
                PHP
            ,
            <<<'PHP'
                <?php

                return random_int();
                PHP
            ,
        ];

        yield 'It removes casting to float in return of untyped-function' => [
            <<<'PHP'
                <?php

                function noReturnType()
                {
                    return (float) random_int();
                }
                PHP,
            <<<'PHP'
                <?php

                function noReturnType()
                {
                    return random_int();
                }
                PHP,
        ];

        yield 'It removes casting to float in return of float-function when strict-types=0' => [
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function returnsFloat(): float
                {
                    return (float) random_int();
                }
                PHP,
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function returnsFloat(): float
                {
                    return random_int();
                }
                PHP,
        ];

        yield 'It not removes casting to float in return of float-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsFloat(): float {
                    return (float) random_int();
                }
                PHP,
        ];

        yield 'It not removes casting to float in nested return of float-function when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function returnsFloat(): float {
                    if (true) {
                        return (float) random_int();
                    }
                    return 1.0;
                }
                PHP,
        ];

        yield 'It removes casting to float in function parameters when strict-types=0' => [
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function doFoo()
                {
                    round((float) $s);
                }
                PHP,
            <<<'PHP'
                <?php

                declare (strict_types=0);
                function doFoo()
                {
                    round($s);
                }
                PHP,
        ];

        yield 'It not removes casting to float in function parameters when strict-types=1' => [
            <<<'PHP'
                <?php declare(strict_types=1);

                function doFoo()
                {
                    round((float) $s);
                }
                PHP,
        ];
    }
}
