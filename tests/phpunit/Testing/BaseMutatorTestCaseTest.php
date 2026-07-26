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

namespace Infection\Tests\Testing;

use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(BaseMutatorTestCase::class)]
final class BaseMutatorTestCaseTest extends TestCase
{
    #[DataProvider('codeToWrapInMethodProvider')]
    public function test_it_can_wrap_code_in_a_method(
        string $code,
        ?string $namespace,
        string $expected,
    ): void {
        $actual = BaseMutatorTestCase::wrapCodeInMethod($code, $namespace);

        $this->assertSame($expected, $actual);
    }

    public static function codeToWrapInMethodProvider(): iterable
    {
        $indent = '    ';

        yield 'simple statement without namespace' => [
            <<<'PHP'
                $a = 1;
                PHP,
            null,
            <<<'PHP'
                <?php

                class WrappingClass {
                    public function wrappedTestedCode() {
                        $a = 1;
                    }
                }
                PHP,
        ];

        yield 'simple statement with namespace' => [
            <<<'PHP'
                $a = 1;
                PHP,
            'App\Domain',
            <<<PHP
                <?php
                namespace App\Domain;
                class WrappingClass {
                    public function wrappedTestedCode() {
                        \$a = 1;
                    }
                }
                PHP,
        ];

        yield 'empty code without namespace' => [
            '',
            null,
            <<<PHP
                <?php

                class WrappingClass {
                    public function wrappedTestedCode() {
                    {$indent}
                    }
                }
                PHP,
        ];

        yield 'empty code with namespace' => [
            '',
            'App',
            <<<PHP
                <?php
                namespace App;
                class WrappingClass {
                    public function wrappedTestedCode() {
                    {$indent}
                    }
                }
                PHP,
        ];

        yield 'multiline code without namespace' => (static function () {
            $secondLineIdent = "\t\x20\x20";

            return [
                <<<PHP
                    \$a = 1;
                    {$secondLineIdent}\$b = 2;
                    PHP,
                null,
                <<<PHP
                    <?php

                    class WrappingClass {
                        public function wrappedTestedCode() {
                            \$a = 1;
                            {$secondLineIdent}\$b = 2;
                        }
                    }
                    PHP,
            ];
        }
        )();

        yield 'code with function call' => [
            <<<'PHP'
                return strlen($foo);
                PHP,
            'App',
            <<<'PHP'
                <?php
                namespace App;
                class WrappingClass {
                    public function wrappedTestedCode() {
                        return strlen($foo);
                    }
                }
                PHP,
        ];
    }
}
