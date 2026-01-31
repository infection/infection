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

use Infection\Mutator\Removal\ReturnRemoval;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ReturnRemoval::class)]
final class ReturnRemovalTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[] $expected
     * @param mixed[] $settings
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield from self::doesNotMutateWithReturnType('array');

        yield from self::doesNotMutateWithReturnType('?array');

        yield from self::doesNotMutateWithReturnType('string|object');

        yield from self::doesNotMutateWithReturnType('array|int|null');

        yield from self::mutatesWithReturnType('void');

        yield from self::mutatesWithReturnType('', '"value"');

        yield 'It mutates duplicate return statements' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class Bar
                    {
                        function foo(): array
                        {
                            return [1];
                            return [2];
                        }
                    }
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    class Bar
                    {
                        function foo(): array
                        {

                            return [2];
                        }
                    }
                    PHP,
            ),
        ];

        yield 'It leaves the last return statement alone if the method has a return type' => [
            <<<'PHP'
                <?php

                namespace Foo;

                class ConfigLoader
                {
                    private ?object $config = null;
                    public function getConfig(): object
                    {
                        if (null !== $this->config) {
                            return $this->config;
                        }
                        // ... load and cache
                        $this->config = $config;
                        return $this->config;
                        // Cool comment
                    }
                    public function foo(): void
                    {
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                namespace Foo;

                class ConfigLoader
                {
                    private ?object $config = null;
                    public function getConfig(): object
                    {
                        if (null !== $this->config) {

                        }
                        // ... load and cache
                        $this->config = $config;
                        return $this->config;
                        // Cool comment
                    }
                    public function foo(): void
                    {
                    }
                }
                PHP,
        ];

        yield 'It does not mutate last return null in function without return type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class Bar
                    {
                        function foo()
                        {
                            $a = 1;
                            return null;
                        }
                    }
                    PHP,
            ),
        ];

        yield 'It does not mutate last empty return in function without return type' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class Bar
                    {
                        function foo()
                        {
                            $a = 1;
                            return;
                        }
                    }
                    PHP,
            ),
        ];

        yield 'It mutates return null if not last statement' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    class Bar
                    {
                        function foo()
                        {
                            if (true) {
                                return null;
                                echo "never reached";
                            }
                            return "default";
                        }
                    }
                    PHP,
            ),
            [
                self::wrapCodeInMethod(
                    <<<'PHP'
                        class Bar
                        {
                            function foo()
                            {
                                if (true) {

                                    echo "never reached";
                                }
                                return "default";
                            }
                        }
                        PHP,
                ),
                self::wrapCodeInMethod(
                    <<<'PHP'
                        class Bar
                        {
                            function foo()
                            {
                                if (true) {
                                    return null;
                                    echo "never reached";
                                }

                            }
                        }
                        PHP,
                ),
            ],
        ];
    }

    private static function mutatesWithReturnType(string $type, string $returnValue = ''): iterable
    {
        $displayType = $type === '' ? 'missing' : $type;
        $methodReturnType = $type === '' ? '' : ": $type";

        yield "It mutates return statements in methods with return type $displayType, value '$returnValue'" => [
            <<<"PHP"
                <?php

                class Bar
                {
                    function foo()$methodReturnType
                    {
                        return $returnValue;
                    }
                }
                PHP,
            <<<"PHP"
                <?php

                class Bar
                {
                    function foo()$methodReturnType
                    {

                    }
                }
                PHP,
        ];
    }

    private static function doesNotMutateWithReturnType(string $type): iterable
    {
        yield "It does not mutate essential return statements with return type $type" => [
            <<<"PHP"
                <?php

                class Bar
                {
                    function foo(): $type
                    {
                        return ThatClass::get();
                    }
                }
                PHP,
        ];
    }
}
