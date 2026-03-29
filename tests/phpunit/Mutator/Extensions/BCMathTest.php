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

namespace Infection\Tests\Mutator\Extensions;

use function array_map;
use function implode;
use Infection\Mutator\Extensions\BCMath;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use function range;
use function strtoupper;
use function ucfirst;

#[CoversClass(BCMath::class)]
final class BCMathTest extends BaseMutatorTestCase
{
    /**
     * @param string|string[]|null $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, string|array|null $expected = [], array $settings = []): void
    {
        $this->assertMutatesInput($input, $expected, $settings);
    }

    public static function mutationsProvider(): iterable
    {
        yield from self::mutationsProviderForBinaryOperator('bcadd', '+', 'summation');

        yield from self::mutationsProviderForBinaryOperator('bcdiv', '/', 'division');

        yield from self::mutationsProviderForBinaryOperator('bcmod', '%', 'modulo');

        yield from self::mutationsProviderForBinaryOperator('bcmul', '*', 'multiplication');

        yield from self::mutationsProviderForBinaryOperator('bcsub', '-', 'subtraction');

        yield from self::mutationsProviderForPowerOperator();

        yield from self::mutationsProviderForSquareRoot();

        yield from self::mutationsProviderForPowerModulo();

        yield from self::mutationsProviderForComparision();
    }

    private static function mutationsProviderForBinaryOperator(string $bcFunc, string $op, string $expression): iterable
    {
        yield "It converts $bcFunc to $expression expression" => [
            self::wrapCodeInMethod("\\$bcFunc('3', \$b);"),
            self::wrapCodeInMethod("(string) ('3' $op \$b);"),
        ];

        $ranmizelyCasedFunction = self::randomizeCase($bcFunc);

        yield "It converts correctly when $bcFunc is wrongly capitalized" => [
            self::wrapCodeInMethod("\\{$ranmizelyCasedFunction}(func(), \$b->test());"),
            self::wrapCodeInMethod("(string) (func() $op \$b->test());"),
        ];

        yield "It converts $bcFunc with scale to $expression expression" => [
            self::wrapCodeInMethod("$bcFunc(CONSTANT, \$b, 2);"),
            self::wrapCodeInMethod("(string) (CONSTANT $op \$b);"),
        ];

        yield from self::provideCasesWhereMutatorShouldNotApply($bcFunc);
    }

    private static function mutationsProviderForPowerOperator(): iterable
    {
        yield 'It converts bcpow to power expression' => [
            self::wrapCodeInMethod('\\bcpow(5, $b);'),
            self::wrapCodeInMethod('(string) 5 ** $b;'),
        ];

        yield 'It converts correctly when bcpow is wrongly capitalized' => [
            self::wrapCodeInMethod('\\bCpOw(5, $b);'),
            self::wrapCodeInMethod('(string) 5 ** $b;'),
        ];

        yield 'It converts bcpow with scale to power expression' => [
            self::wrapCodeInMethod('bcpow($a, $b, 2);'),
            self::wrapCodeInMethod('(string) $a ** $b;'),
        ];

        yield from self::provideCasesWhereMutatorShouldNotApply('bcpow');
    }

    private static function mutationsProviderForSquareRoot(): iterable
    {
        yield 'It converts bcsqrt to sqrt call' => [
            self::wrapCodeInMethod('\\bcsqrt(2);'),
            self::wrapCodeInMethod('(string) \\sqrt(2);'),
        ];

        yield 'It converts correctly when bcsqrt is wrongly capitalized' => [
            self::wrapCodeInMethod('\\BCsqRt($a);'),
            self::wrapCodeInMethod('(string) \\sqrt($a);'),
        ];

        yield 'It converts bcsqrt with scale to sqrt call' => [
            self::wrapCodeInMethod('bcsqrt($a, 2);'),
            self::wrapCodeInMethod('(string) \\sqrt($a);'),
        ];

        yield from self::provideCasesWhereMutatorShouldNotApply('bcsqrt', 1);
    }

    private static function mutationsProviderForPowerModulo(): iterable
    {
        yield 'It converts bcpowmod to power modulo expression' => [
            self::wrapCodeInMethod('\\bcpowmod($a, $b, $mod);'),
            self::wrapCodeInMethod('(string) (\\pow($a, $b) % $mod);'),
        ];

        yield 'It converts correctly when bcpowmod is wrongly capitalized' => [
            self::wrapCodeInMethod('\\BcPowMod($a, $b, $mod);'),
            self::wrapCodeInMethod('(string) (\\pow($a, $b) % $mod);'),
        ];

        yield 'It converts bcpowmod with scale to power modulo expression' => [
            self::wrapCodeInMethod('bcpowmod($a, $b, 2);'),
            self::wrapCodeInMethod('(string) (\\pow($a, $b) % 2);'),
        ];

        yield from self::provideCasesWhereMutatorShouldNotApply('bcpowmod', 3);
    }

    private static function mutationsProviderForComparision(): iterable
    {
        yield 'It converts bccomp to spaceship expression' => [
            self::wrapCodeInMethod("\\bccomp('3', \$b);"),
            self::wrapCodeInMethod("'3' <=> \$b;"),
        ];

        yield 'It converts correctly when bccomp is wrongly capitalized' => [
            self::wrapCodeInMethod('\\bCCoMp(func(), $b->test());'),
            self::wrapCodeInMethod('func() <=> $b->test();'),
        ];

        yield 'It converts bccomp with scale to spaceship expression' => [
            self::wrapCodeInMethod('bccomp(CONSTANT, $b, 2);'),
            self::wrapCodeInMethod('CONSTANT <=> $b;'),
        ];

        yield from self::provideCasesWhereMutatorShouldNotApply('bccomp', 2);
    }

    private static function provideCasesWhereMutatorShouldNotApply(string $bcFunc, int $requiredArgumentsCount = 2): iterable
    {
        $invalidArgumentsExpression = self::generateArgumentsExpression($requiredArgumentsCount - 1);
        $validArgumentsExpression = self::generateArgumentsExpression($requiredArgumentsCount);

        yield "It does not convert $bcFunc when no enough arguments" => [
            self::wrapCodeInMethod("$bcFunc($invalidArgumentsExpression);"),
        ];

        yield "It does not mutate $bcFunc called via variable" => [
            self::wrapCodeInMethod("\$a = '$bcFunc'; \$a($validArgumentsExpression);"),
        ];

        yield "It does not convert $bcFunc when disabled" => [
            self::wrapCodeInMethod("$bcFunc($validArgumentsExpression);"),
            null,
            [$bcFunc => false],
        ];
    }

    private static function randomizeCase(string $bcFunc): string
    {
        $bcFunc[2] = strtoupper($bcFunc[2]);
        $bcFunc[4] = strtoupper($bcFunc[4]);

        return ucfirst($bcFunc);
    }

    private static function generateArgumentsExpression(int $numberOfArguments): string
    {
        return implode(', ', array_map(static fn (string $argument): string => "'$argument'", $numberOfArguments > 0 ? range(1, $numberOfArguments) : []));
    }
}
