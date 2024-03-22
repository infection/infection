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

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Infection\Mutator\Operator\ElseIfNegation::class)]
final class ElseIfNegationTest extends BaseMutatorTestCase
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
        yield 'It preserve if and else expression' => [
            <<<'PHP'
                <?php

                if ($this->fooBar()) {
                    return 1;
                } elseif ($this->barFoo()) {
                    return 2;
                } else {
                    return 3;
                }
                PHP
            ,
            <<<'PHP'
                <?php

                if ($this->fooBar()) {
                    return 1;
                } elseif (!$this->barFoo()) {
                    return 2;
                } else {
                    return 3;
                }
                PHP,
        ];

        yield 'It mutates array item fetch' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif ($array[0]) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!$array[0]) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates variable' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif ($foo) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!$foo) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates method call' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif ($this->foo()) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!$this->foo()) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates static calls' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif (self::foo()) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!self::foo()) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates constant calls' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif (self::FOO) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!self::FOO) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates closure calls' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif ($foo()) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!$foo()) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates invoke calls' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif (($this->foo)()) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!($this->foo)()) {
                    }
                    PHP,
            ],
        ];

        yield 'It mutates function calls' => [
            <<<'PHP'
                <?php

                if (true) {
                } elseif (a()) {
                }
                PHP
            ,
            [
                <<<'PHP'
                    <?php

                    if (true) {
                    } elseif (!a()) {
                    }
                    PHP,
            ],
        ];

        yield 'It does not mutate already negated expression' => [
            <<<'PHP'
                <?php

                if ($this->barFoo()) {
                } elseif (!$this->fooBar()) {
                }
                PHP,
        ];

        yield 'It does not mutate equal comparison' => [
            <<<'PHP'
                <?php

                if ($this->barFoo()) {
                } elseif ($this->fooBar() == 1) {
                }
                PHP,
        ];

        yield 'It does not mutate not equal comparison' => [
            <<<'PHP'
                <?php

                if ($this->barFoo()) {
                } elseif ($this->fooBar() != 1) {
                }
                PHP,
        ];

        yield 'It does not mutate identical comparison' => [
            <<<'PHP'
                <?php

                if ($this->barFoo()) {
                } elseif ($this->fooBar() === true) {
                }
                PHP,
        ];

        yield 'It does not mutate not identical comparison' => [
            <<<'PHP'
                <?php

                if ($this->barFoo()) {
                } elseif ($this->fooBar() !== true) {
                }
                PHP,
        ];

        yield 'It does not mutate and condition' => [
            <<<'PHP'
                <?php

                if (c()) {
                } elseif (a() && b()) {
                }
                PHP,
        ];

        yield 'It does not mutate or condition' => [
            <<<'PHP'
                <?php

                if (c()) {
                } elseif (a() || b()) {
                }
                PHP,
        ];
    }
}
