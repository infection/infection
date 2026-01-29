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

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[CoversClass(PublicVisibility::class)]
final class PublicVisibilityTest extends BaseMutatorTestCase
{
    #[DataProvider('blacklistedProvider')]
    public function test_it_does_not_modify_blacklisted_functions(string $functionName): void
    {
        $code = MutatorFixturesProvider::getFixtureFileContent(self::class, "pv-{$functionName}.php");

        $this->assertMutatesInput($code);
    }

    public static function blacklistedProvider(): iterable
    {
        yield ['__construct'];

        yield ['__invoke'];

        yield ['__call'];

        yield ['__callStatic'];

        yield ['__get'];

        yield ['__set'];

        yield ['__isset'];

        yield ['__unset'];

        yield ['__toString'];

        yield ['__debugInfo'];
    }

    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates public to protected' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-one-class.php'),
            <<<'PHP'
                <?php

                namespace PublicVisibilityOneClass;

                class Test
                {
                    protected function &foo(int $param, $test = 1): bool
                    {
                        echo 1;
                        return false;
                    }
                }

                PHP,
        ];

        yield 'It does not mutate final flag' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-final.php'),
            <<<'PHP'
                <?php

                namespace PublicVisibilityFinal;

                class Test
                {
                    final protected function foo(int $param, $test = 1): bool
                    {
                        echo 1;
                        return false;
                    }
                }
                PHP,
        ];

        yield 'It mutates non abstract public to protected in an abstract class' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-non-abstract-in-abstract-class.php'),
            <<<'PHP'
                <?php

                namespace PublicVisibilityNonAbstractInAbstractClass;

                abstract class Test
                {
                    protected function foo(int $param, $test = 1) : bool
                    {
                        echo 1;
                        return false;
                    }
                }
                PHP,
        ];

        yield 'It does not mutate static flag' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-static.php'),
            <<<'PHP'
                <?php

                namespace PublicVisibilityStatic;

                class Test
                {
                    protected static function foo(int $param, $test = 1): bool
                    {
                        echo 1;
                        return false;
                    }
                }
                PHP,
        ];

        yield 'It replaces visibility if not set' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-not-set.php'),
            <<<'PHP'
                <?php

                namespace PublicVisibilityNotSet;

                class Test
                {
                    protected function foo() {}
                }
                PHP,
        ];

        yield 'It does not mutate an interface' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-interface.php'),
        ];

        yield 'It does not mutate an abstract function' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-abstract.php'),
        ];

        yield 'It does not mutate if interface has same public method' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-same-method-interface.php'),
        ];

        yield 'It does not mutate if any of interfaces has same public method' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-same-method-any-interface.php'),
        ];

        yield 'It does not mutate if parent abstract has same public method' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-same-method-abstract.php'),
        ];

        yield 'It does not mutate if parent class has same public method' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-same-method-parent.php'),
            <<<'PHP'
                <?php

                namespace SameParent;

                class SameParent
                {
                    protected function foo()
                    {
                    }
                }
                class Child extends SameParent
                {
                    public function foo()
                    {
                    }
                }
                PHP,
        ];

        yield 'it does not mutate if grandparent class has same public method' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-same-method-grandparent.php'),
            <<<'PHP'
                <?php

                namespace SameGrandParent;

                class GrandParent
                {
                    protected function foo() {}
                }

                class SameParent extends GrandParent
                {
                }

                class Child extends SameParent
                {
                    public function foo() {}
                }
                PHP,
        ];

        yield 'it does mutate non-inherited methods' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-non-same-method-parent.php'),
            <<<'PHP'
                <?php

                namespace NonSameAbstract;

                abstract class NonSameAbstract
                {
                    public abstract function foo();
                }
                class Child extends NonSameAbstract
                {
                    public function foo()
                    {
                    }
                    protected function bar()
                    {
                    }
                }

                PHP,
        ];

        yield 'it mutates an anonymous class' => [
            <<<'PHP'
                <?php

                function something()
                {
                    return new class
                    {
                        public function anything()
                        {
                            return null;
                        }
                    };
                }
                PHP,
            <<<'PHP'
                <?php

                function something()
                {
                    return new class
                    {
                        protected function anything()
                        {
                            return null;
                        }
                    };
                }
                PHP,
        ];

        yield 'It does mutate when the parents method is protected' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'pv-protected-parent.php'),
            <<<'PHP'
                <?php

                namespace ProtectedParent;

                abstract class SameAbstract
                {
                    protected abstract function foo();
                }
                class Child extends SameAbstract
                {
                    protected function foo()
                    {
                    }
                }

                PHP,
        ];

        yield 'It does mutate anonymous classes' => [
            <<<'PHP'
                <?php

                $var = [
                    'class' => new class() {
                        public function foo(): bool
                        {
                            return true;
                        }
                    },
                ];
                PHP,
            <<<'PHP'
                <?php

                $var = [
                    'class' => new class() {
                        protected function foo(): bool
                        {
                            return true;
                        }
                    },
                ];
                PHP,
        ];

        yield 'It does not remove attributes' => [
            <<<'PHP'
                <?php

                namespace PublicVisibilityOneClass;

                class Test
                {
                    #[SomeAttribute1]
                    #[SomeAttribute2]
                    public function &foo(int $param, $test = 1): bool
                    {
                        echo 1;
                        return false;
                    }
                }
                PHP,
            <<<'PHP'
                <?php

                namespace PublicVisibilityOneClass;

                class Test
                {
                    #[SomeAttribute1]
                    #[SomeAttribute2]
                    protected function &foo(int $param, $test = 1): bool
                    {
                        echo 1;
                        return false;
                    }
                }
                PHP,
        ];
    }
}
