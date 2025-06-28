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
     * @param string|string[] $expected
     */
    #[DataProvider('mutationsProvider')]
    public function test_it_can_mutate(string $input, $expected = []): void
    {
        $this->assertMutatesInput($input, $expected);
    }

    public static function mutationsProvider(): iterable
    {
        yield 'It mutates logical or' => [
            <<<'PHP'
                <?php

                true || false;
                PHP
            ,
            <<<'PHP'
                <?php

                true && false;
                PHP
            ,
        ];

        yield 'It does not mutate logical lower or' => [
            <<<'PHP'
                <?php

                true or false;
                PHP
            ,
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
            <<<'PHP'
                <?php

                $myVar === 'hello' || $myVar === 'world';
                PHP
            ,
        ];

        // TODO : improve this to mutate only if checking for falsy values on both sides.
        yield 'It does mutate logical or if same variable is tested against "Equal".' => [
            <<<'PHP'
                <?php

                $myVar == 'hello' || $myVar == 'world';
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar == 'hello' && $myVar == 'world';
                PHP,
        ];

        yield 'It does mutate logical or if same variable is tested against "Equal" & "Identical".' => [
            <<<'PHP'
                <?php

                $myVar === 'hello' || $myVar == 'world';
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar === 'hello' && $myVar == 'world';
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Identical" (mirrored #1).' => [
            <<<'PHP'
                <?php

                $myVar === 'hello' || 'world' === $myVar;
                PHP
            ,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Identical" (mirrored #2).' => [
            <<<'PHP'
                <?php

                'world' === $myVar || $myVar === 'hello';
                PHP
            ,
        ];

        yield 'It mutates logical or if variables names are different' => [
            <<<'PHP'
                <?php

                $myVar === true || $myOtherVar === false;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar === true && $myOtherVar === false;
                PHP
            ,
        ];
    }

    private static function nonMutableSmallerAndGreaterMatrixMutationsProvider(): iterable
    {
        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #1.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar > 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #2.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar > 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #3.' => [
            <<<'PHP'
                <?php

                $myVar < 5.5 || $myVar > 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "Greater" #4.' => [
            <<<'PHP'
                <?php

                $myVar < 5.5 || $myVar > 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #1.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar >= 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #2.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar >= 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #3.' => [
            <<<'PHP'
                <?php

                $myVar < 5.5 || $myVar >= 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "Smaller" and "GreaterOrEqual" #4.' => [
            <<<'PHP'
                <?php

                $myVar < 5.5 || $myVar >= 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #1.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar >= 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #2.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar >= 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #3.' => [
            <<<'PHP'
                <?php

                $myVar <= 5.5 || $myVar >= 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" #4.' => [
            <<<'PHP'
                <?php

                $myVar <= 5.5 || $myVar >= 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #1.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar > 10;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #2.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar > 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #3.' => [
            <<<'PHP'
                <?php

                $myVar <= 5.5 || $myVar > 10.1;
                PHP,
        ];

        yield 'It does not mutate logical or if same variable is tested against "SmallerOrEqual" and "Greater" #4.' => [
            <<<'PHP'
                <?php

                $myVar <= 5.5 || $myVar > 10;
                PHP,
        ];
    }

    private static function mutableSmallerAndGreaterMatrixMutationsProvider(): iterable
    {
        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #1.' => [
            <<<'PHP'
                <?php

                $myVar < 10 || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10 && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #2.' => [
            <<<'PHP'
                <?php

                $myVar < 10 || $myVar > 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10 && $myVar > 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #3.' => [
            <<<'PHP'
                <?php

                $myVar < 10.1 || $myVar > 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10.1 && $myVar > 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values permits it #4.' => [
            <<<'PHP'
                <?php

                $myVar < 10.1 || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10.1 && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #1.' => [
            <<<'PHP'
                <?php

                $myVar < 10 || $myVar >= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10 && $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #2.' => [
            <<<'PHP'
                <?php

                $myVar < 10 || $myVar >= 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10 && $myVar >= 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #3.' => [
            <<<'PHP'
                <?php

                $myVar < 10.1 || $myVar >= 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10.1 && $myVar >= 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values permits it #4.' => [
            <<<'PHP'
                <?php

                $myVar < 10.1 || $myVar >= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar < 10.1 && $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #1.' => [
            <<<'PHP'
                <?php

                $myVar <= 10 || $myVar >= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10 && $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #2.' => [
            <<<'PHP'
                <?php

                $myVar <= 10 || $myVar >= 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10 && $myVar >= 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #3.' => [
            <<<'PHP'
                <?php

                $myVar <= 10.1 || $myVar >= 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10.1 && $myVar >= 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values permits it #4.' => [
            <<<'PHP'
                <?php

                $myVar <= 10.1 || $myVar >= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10.1 && $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #1.' => [
            <<<'PHP'
                <?php

                $myVar <= 10 || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10 && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #2.' => [
            <<<'PHP'
                <?php

                $myVar <= 10 || $myVar > 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10 && $myVar > 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #3.' => [
            <<<'PHP'
                <?php

                $myVar <= 10.1 || $myVar > 5.5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10.1 && $myVar > 5.5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values permits it #4.' => [
            <<<'PHP'
                <?php

                $myVar <= 10.1 || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 10.1 && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or when used with 2 variables' => [
            <<<'PHP'
                <?php

                $myVar <= $other || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= $other && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or when used with 2 variables (inverse)' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar > $other;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 5 && $myVar > $other;
                PHP,
        ];

        yield 'It mutates logical or when used with variable variables' => [
            <<<'PHP'
                <?php

                $s = 'other';
                $myVar <= ${$s} || $myVar > 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $s = 'other';
                $myVar <= ${$s} && $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or when used with variable variables (inverse)' => [
            <<<'PHP'
                <?php

                $s = 'other';
                $myVar <= 5 || $myVar > ${$s};
                PHP
            ,
            <<<'PHP'
                <?php

                $s = 'other';
                $myVar <= 5 && $myVar > ${$s};
                PHP,
        ];
    }

    private static function smallerAndGreaterMatrixWithSameValueMutationsProvider(): iterable
    {
        yield 'It mutates logical or if same variable is tested against "Smaller" and "Greater" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Greater" and "Smaller" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar > 5 || $myVar < 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Smaller" and "GreaterOrEqual" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar < 5 || $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "GreaterOrEqual" and "Smaller" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar >= 5 || $myVar < 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "GreaterOrEqual" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar >= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar <= 5 && $myVar >= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "GreaterOrEqual" and "SmallerOrEqual" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar >= 5 || $myVar <= 5;
                PHP
            ,
            <<<'PHP'
                <?php

                $myVar >= 5 && $myVar <= 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "SmallerOrEqual" and "Greater" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar <= 5 || $myVar > 5;
                PHP,
        ];

        yield 'It mutates logical or if same variable is tested against "Greater" and "SmallerOrEqual" and values are the same.' => [
            <<<'PHP'
                <?php

                $myVar > 5 || $myVar <= 5;
                PHP,
        ];
    }

    private static function instanceOfMutationsProvider(): iterable
    {
        yield 'It mutates negated instanceof with 1 concrete class and 1 trait' => [
            <<<'PHP'
                <?php

                $var = $node instanceof Node\Expr\PostDec || $node instanceof Infection\Tests\Mutant\MutantAssertions;
                PHP
            ,
            <<<'PHP'
                <?php

                $var = $node instanceof Node\Expr\PostDec && $node instanceof Infection\Tests\Mutant\MutantAssertions;
                PHP
            ,
        ];

        yield 'It mutates negated instanceof with 1 concrete class and 1 interface' => [
            <<<'PHP'
                <?php

                $var = $node instanceof \Countable || $node instanceof Node\Expr\PostDec;
                PHP
            ,
            <<<'PHP'
                <?php

                $var = $node instanceof \Countable && $node instanceof Node\Expr\PostDec;
                PHP
            ,
        ];

        yield 'It mutates negated instanceof with 1 concrete class and 1 interface (inverse)' => [
            <<<'PHP'
                <?php

                $var = $node instanceof Node\Expr\PostDec || $node instanceof \Countable;
                PHP
            ,
            <<<'PHP'
                <?php

                $var = $node instanceof Node\Expr\PostDec && $node instanceof \Countable;
                PHP
            ,
        ];

        yield 'It mutates negated instanceof with 2 concrete classes (different variables)' => [
            <<<'PHP'
                <?php

                $var = $node1 instanceof PhpParser\Node\Expr\PreDec || $node2 instanceof PhpParser\Node\Expr\PostDec;
                PHP
            ,
            <<<'PHP'
                <?php

                $var = $node1 instanceof PhpParser\Node\Expr\PreDec && $node2 instanceof PhpParser\Node\Expr\PostDec;
                PHP
            ,
        ];

        yield 'It mutates variable instanceof' => [
            <<<'PHP'
                <?php

                $class = PhpParser\Node\Expr\PreDec::class;
                $var = $node1 instanceof $class || $node1 instanceof PhpParser\Node\Expr\PostDec;
                PHP
            ,
            <<<'PHP'
                <?php

                $class = PhpParser\Node\Expr\PreDec::class;
                $var = $node1 instanceof $class && $node1 instanceof PhpParser\Node\Expr\PostDec;
                PHP
            ,
        ];

        yield 'It does not mutate negated instanceof with 2 concrete classes (same variable)' => [
            <<<'PHP'
                <?php

                $var = $node instanceof PhpParser\Node\Expr\PreDec || $node instanceof PhpParser\Node\Expr\PostDec;
                PHP
            ,
        ];
    }
}
