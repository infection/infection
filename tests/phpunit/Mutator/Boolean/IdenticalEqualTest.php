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

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\IdenticalEqual;
use Infection\Testing\BaseMutatorTestCase;
use Infection\Tests\Mutator\MutatorFixturesProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(IdenticalEqual::class)]
final class IdenticalEqualTest extends BaseMutatorTestCase
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
        yield 'It mutates identical operator into equal operator with two variables' => [
            <<<'PHP'
                <?php

                $a === $b;
                PHP
            ,
            <<<'PHP'
                <?php

                $a == $b;
                PHP
            ,
        ];

        yield 'It mutates identical operator into equal operator with type casting' => [
            <<<'PHP'
                <?php

                (int) $c === 2;
                PHP
            ,
            <<<'PHP'
                <?php

                (int) $c == 2;
                PHP
            ,
        ];

        yield 'It mutates identical operator into equal operator with variable and null' => [
            <<<'PHP'
                <?php

                $d === null;
                PHP
            ,
            <<<'PHP'
                <?php

                $d == null;
                PHP
            ,
        ];

        yield 'It mutates identical operator into equal operator with boolean and function call' => [
            <<<'PHP'
                <?php

                false === strpos();
                PHP
            ,
            <<<'PHP'
                <?php

                false == strpos();
                PHP
            ,
        ];

        yield 'It mutates identical operator into equal operator for maybe same type operations (string)' => [
            <<<'PHP'
                <?php

                $var === trim();
                PHP,
            <<<'PHP'
                <?php

                $var == trim();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for maybe same type operations with inverse operands (string)' => [
            <<<'PHP'
                <?php

                trim() === $var;
                PHP,
            <<<'PHP'
                <?php

                trim() == $var;
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations (string)' => [
            <<<'PHP'
                <?php

                '' === trim();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for inverse same type operations (string)' => [
            <<<'PHP'
                <?php

                trim() === '';
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations (bool)' => [
            <<<'PHP'
                <?php

                false === is_array();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for inverse same type operations (bool)' => [
            <<<'PHP'
                <?php

                is_array() === false;
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations (true)' => [
            <<<'PHP'
                <?php

                true === is_array();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for inverse same type operations (true)' => [
            <<<'PHP'
                <?php

                is_array() === true;
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations (int)' => [
            <<<'PHP'
                <?php

                5 === random_int();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for inverse same type operations (int)' => [
            <<<'PHP'
                <?php

                random_int() === 5;
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations (float)' => [
            <<<'PHP'
                <?php

                3.0 === round();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for inverse same type operations (float)' => [
            <<<'PHP'
                <?php

                round() === 3.0;
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for different type operations with function operands (string)' => [
            <<<'PHP'
                <?php

                strchr() === substr();
                PHP,
            <<<'PHP'
                <?php

                strchr() == substr();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for same type operations with function operands (string)' => [
            <<<'PHP'
                <?php

                trim() === substr();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for incompatible types in operation' => [
            <<<'PHP'
                <?php

                true === trim();
                PHP,
            <<<'PHP'
                <?php

                true == trim();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for incompatible types in operation (inversed)' => [
            <<<'PHP'
                <?php

                trim() === false;
                PHP,
            <<<'PHP'
                <?php

                trim() == false;
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for union type operations with falsy operand' => [
            <<<'PHP'
                <?php

                preg_match() === 0;
                PHP,
            <<<'PHP'
                <?php

                preg_match() == 0;
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for union type operations with falsy operand (inverse)' => [
            <<<'PHP'
                <?php

                0 === preg_match();
                PHP,
            <<<'PHP'
                <?php

                0 == preg_match();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for union type operations with non-falsy operand' => [
            <<<'PHP'
                <?php

                preg_match() === 1;
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for union type operations with non-falsy operand (inverse)' => [
            <<<'PHP'
                <?php

                1 === preg_match();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for union type operations which values cannot be narrowed to a single type' => [
            <<<'PHP'
                <?php

                array_key_last() === null;
                PHP,
            <<<'PHP'
                <?php

                array_key_last() == null;
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for comparison of union type operands' => [
            <<<'PHP'
                <?php

                preg_match() === array_key_last();
                PHP,
            <<<'PHP'
                <?php

                preg_match() == array_key_last();
                PHP,
        ];

        yield 'It mutates identical operator of non-reflectable functions' => [
            <<<'PHP'
                <?php

                nooneKnowsThisFunction() === nooneKnowsThisFunction();
                PHP,
            <<<'PHP'
                <?php

                nooneKnowsThisFunction() == nooneKnowsThisFunction();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for comparison of intersection types' => [
            MutatorFixturesProvider::getFixtureFileContent(self::class, 'identical-intersection-type.php'),
            <<<'PHP'
                <?php

                namespace IdenticalEqualIntersectionType;

                interface A
                {
                }
                interface B
                {
                }
                class C implements A, B
                {
                }
                function doFoo(): A&B
                {
                    return new C();
                }
                doFoo() == doFoo();
                doFoo() == doFoo();
                PHP,
        ];

        yield 'It not mutates identical operator into equal operator for empty array type' => [
            <<<'PHP'
                <?php

                [] === explode();
                PHP,
        ];

        yield 'It mutates identical operator into equal operator for non-empty-array type' => [
            <<<'PHP'
                <?php

                ['abc'] === explode();
                PHP,
            <<<'PHP'
                <?php

                ['abc'] == explode();
                PHP,
        ];
    }
}
