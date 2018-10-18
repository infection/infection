<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Unwrap;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class UnwrapArrayFilterTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates correctly when provided with a array' => [
            <<<'PHP'
<?php

$a = array_filter(['A', 1, 'C'], 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
PHP
        ];

        yield 'It mutates correctly when provided with a constant' => [
            <<<'PHP'
<?php

$a = array_filter(\Class_With_Const::Const, 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when a backslash is in front of array_filter' => [
            <<<'PHP'
<?php

$a = \array_filter(['A', 1, 'C'], 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
PHP
        ];

        yield 'It does not mutate other array_ calls' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', ['A', 'B', 'C']);
PHP
        ];

        yield 'It does not mutate functions named array_filter' => [
            <<<'PHP'
<?php

function array_filter($text, $other)
{
}
PHP
        ];

        yield 'It mutates correctly within if statements' => [
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
if (array_filter($a, 'is_int') === $a) {
    return true;
}
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
if ($a === $a) {
    return true;
}
PHP
        ];

        yield 'It mutates correctly when array_map is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = aRrAy_FiLtEr(['A', 1, 'C'], 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
PHP
        ];

        yield 'It mutates correctly when array_map uses another function as input' => [
            <<<'PHP'
<?php

$a = array_filter($foo->bar(), 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = $foo->bar();
PHP
        ];

        yield 'It mutates correctly when provided with a more complex situation' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', array_filter(['A', 1, 'C'], function($char): bool {
    return !is_int($char);
}));
PHP
            ,
            <<<'PHP'
<?php

$a = array_map('strtolower', ['A', 1, 'C']);
PHP
        ];
    }
}
