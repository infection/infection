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
final class UnwrapArrayMapTest extends AbstractMutatorTestCase
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

$a = array_map('strtolower', ['A', 'B', 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
PHP
        ];

        yield 'It mutates correctly when provided with a constant' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', \Class_With_Const::Const);
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when a backslash is in front of array_map' => [
            <<<'PHP'
<?php

$a = \array_map('strtolower', ['A', 'B', 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
PHP
        ];

        yield 'It does not mutate other array_ calls' => [
            <<<'PHP'
<?php

$a = array_filter([1, 2, 3], 'is_int');
PHP
        ];

        yield 'It does not mutate functions named array_map' => [
            <<<'PHP'
<?php

function array_map($text, $other)
{
}
PHP
        ];

        yield 'It mutates correctly within if statements' => [
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
if (array_map('strtolower', $a) === $a) {
    return true;
}
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
if ($a === $a) {
    return true;
}
PHP
        ];

        yield 'It mutates correctly when array_map is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = ArRaY_mAp('strtolower', ['A', 'B', 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
PHP
        ];

        yield 'It mutates correctly when array_map uses another function as input' => [
            <<<'PHP'
<?php

$a = array_map('strtolower', $foo->bar());
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

$a = array_filter(array_map(function(string $letter): string {
    return strtolower($letter);
}, ['A', 'B', 'C']), 'is_int');
PHP
            ,
            <<<'PHP'
<?php

$a = array_filter(['A', 'B', 'C'], 'is_int');
PHP
        ];
    }
}
