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

use Infection\Mutator\Boolean\LogicalOr;
use Infection\Testing\BaseMutatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(LogicalOr::class)]
final class LogicalOrTest extends BaseMutatorTestCase
{
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
        yield 'It mutates logical or' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    true || false;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    true && false;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical lower or' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    true or false;
                    PHP,
            ),
        ];

        yield from self::equalityMutationsProvider();

        yield from self::nonMutableSmallerAndGreaterMatrixMutationsProvider();

        yield from self::mutableSmallerAndGreaterMatrixMutationsProvider();

        yield from self::smallerAndGreaterMatrixWithSameValueMutationsProvider();

        yield from self::instanceOfMutationsProvider();
    }

    private static function equalityMutationsProvider(): iterable
    {
        yield 'It does not mutate logical or if same variable is tested against "Identical".' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === 'hello' || $myVar === 'world';
                    PHP,
            ),
        ];

        // TODO : improve this to mutate only if checking for falsy values on both sides.
        yield 'It does mutate logical or if same variable is tested against "Equal".' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar == 'hello' || $myVar == 'world';
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar == 'hello' && $myVar == 'world';
                    PHP,
            ),
        ];

        yield 'It does mutate logical or if same variable is tested against "Equal" & "Identical".' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === 'hello' || $myVar == 'world';
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === 'hello' && $myVar == 'world';
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Identical" (mirrored #1).' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === 'hello' || 'world' === $myVar;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Identical" (mirrored #2).' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    'world' === $myVar || $myVar === 'hello';
                    PHP,
            ),
        ];

        yield 'It mutates logical or if variables names are different' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === true || $myOtherVar === false;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar === true && $myOtherVar === false;
                    PHP,
            ),
        ];
    }

    private static function nonMutableSmallerAndGreaterMatrixMutationsProvider(): iterable
    {
        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar > 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar > 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5.5 || $myVar > 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5.5 || $myVar > 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar >= 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar >= 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5.5 || $myVar >= 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5.5 || $myVar >= 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar >= 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar >= 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5.5 || $myVar >= 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5.5 || $myVar >= 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar > 10;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar > 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5.5 || $myVar > 10.1;
                    PHP,
            ),
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5.5 || $myVar > 10;
                    PHP,
            ),
        ];
    }

    private static function mutableSmallerAndGreaterMatrixMutationsProvider(): iterable
    {
        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 || $myVar > 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 && $myVar > 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 || $myVar > 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 && $myVar > 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 || $myVar >= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 && $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 || $myVar >= 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10 && $myVar >= 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 || $myVar >= 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 && $myVar >= 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 || $myVar >= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 10.1 && $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 || $myVar >= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 && $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 || $myVar >= 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 && $myVar >= 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 || $myVar >= 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 && $myVar >= 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 || $myVar >= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 && $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #1.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #2.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 || $myVar > 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10 && $myVar > 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #3.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 || $myVar > 5.5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 && $myVar > 5.5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #4.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 10.1 && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or when used with 2 variables' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= $other || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= $other && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or when used with 2 variables (inverse)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar > $other;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 && $myVar > $other;
                    PHP,
            ),
        ];

        yield 'It mutates logical or when used with variable variables' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $s = 'other';
                    $myVar <= ${$s} || $myVar > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $s = 'other';
                    $myVar <= ${$s} && $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or when used with variable variables (inverse)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $s = 'other';
                    $myVar <= 5 || $myVar > ${$s};
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $s = 'other';
                    $myVar <= 5 && $myVar > ${$s};
                    PHP,
            ),
        ];
    }

    private static function smallerAndGreaterMatrixWithSameValueMutationsProvider(): iterable
    {
        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Greater" and "Smaller" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar > 5 || $myVar < 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar < 5 || $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "GreaterOrEqual" and "Smaller" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar >= 5 || $myVar < 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar >= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 && $myVar >= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "GreaterOrEqual" and "SmallerOrEqual" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar >= 5 || $myVar <= 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar >= 5 && $myVar <= 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar <= 5 || $myVar > 5;
                    PHP,
            ),
        ];

        yield 'It mutates logical or if same variable is tested against "Greater" and "SmallerOrEqual" and values are the same.' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $myVar > 5 || $myVar <= 5;
                    PHP,
            ),
        ];
    }

    private static function instanceOfMutationsProvider(): iterable
    {
        yield 'It mutates negated instanceof with 1 concrete class and 1 trait' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof Node\Expr\PostDec || $node instanceof Infection\Tests\Mutant\MutantAssertions;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof Node\Expr\PostDec && $node instanceof Infection\Tests\Mutant\MutantAssertions;
                    PHP,
            ),
        ];

        yield 'It mutates negated instanceof with 1 concrete class and 1 interface' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof \Countable || $node instanceof Node\Expr\PostDec;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof \Countable && $node instanceof Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It mutates negated instanceof with 1 concrete class and 1 interface (inverse)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof Node\Expr\PostDec || $node instanceof \Countable;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof Node\Expr\PostDec && $node instanceof \Countable;
                    PHP,
            ),
        ];

        yield 'It mutates negated instanceof with 2 concrete classes (different variables)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node1 instanceof PhpParser\Node\Expr\PreDec || $node2 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node1 instanceof PhpParser\Node\Expr\PreDec && $node2 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It mutates variable instanceof' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class = PhpParser\Node\Expr\PreDec::class;
                    $var = $node1 instanceof $class || $node1 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $class = PhpParser\Node\Expr\PreDec::class;
                    $var = $node1 instanceof $class && $node1 instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It does not mutate negated instanceof with 2 concrete classes (same variable)' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof PhpParser\Node\Expr\PreDec || $node instanceof PhpParser\Node\Expr\PostDec;
                    PHP,
            ),
        ];

        yield 'It mutates left-side instanceof or non-instanceof expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof \Countable || $i > 5;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $node instanceof \Countable && $i > 5;
                    PHP,
            ),
        ];

        yield 'It mutates right-side instanceof or non-instanceof expression' => [
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $i > 5 || $node instanceof \Countable;
                    PHP,
            ),
            self::wrapCodeInMethod(
                <<<'PHP'
                    $var = $i > 5 && $node instanceof \Countable;
                    PHP,
            ),
        ];
    }
}
