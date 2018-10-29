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
final class UnwrapArrayReverseTest extends AbstractMutatorTestCase
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
        yield 'It mutates correctly when provided with an array' => [
            <<<'PHP'
<?php

$a = array_reverse(['A', 1, 'C']);
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

$a = array_reverse(\Class_With_Const::Const);
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when a backslash is in front of array_reverse' => [
            <<<'PHP'
<?php

$a = \array_reverse(['A', 1, 'C']);
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

        yield 'It does not mutate functions named array_reverse' => [
            <<<'PHP'
<?php

function array_reverse($array)
{
}
PHP
        ];

        yield 'It mutates correctly within if statements' => [
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
if (array_reverse($a) === $a) {
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

        yield 'It mutates correctly when array_reverse is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = aRrAy_ReVeRsE(['A', 1, 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
PHP
        ];

        yield 'It mutates correctly when array_reverse uses another function as input' => [
            <<<'PHP'
<?php

$a = array_reverse($foo->bar());
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

$a = array_map('strtolower', array_reverse(['A', 1, 'C']));
PHP
            ,
            <<<'PHP'
<?php

$a = array_map('strtolower', ['A', 1, 'C']);
PHP
        ];

        yield 'It mutates correctly when the $preserve_keys parameter is present' => [
            <<<'PHP'
<?php

$a = array_reverse(['A', 1, 'C'], $preserve_keys);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 1, 'C'];
PHP
        ];
    }
}
