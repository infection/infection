<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Mutator\Regex;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class PregQuoteTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates correctly when provided with a string' => [
            <<<'PHP'
<?php

$a = preg_quote('bbbb');
PHP
            ,
            <<<'PHP'
<?php

$a = 'bbbb';
PHP
        ];

        yield 'It mutates correctly when provided with a variable' => [
            <<<'PHP'
<?php

$a = 'to_quote';
$a = preg_quote($a);
PHP
            ,
            <<<'PHP'
<?php

$a = 'to_quote';
$a = $a;
PHP
        ];

        yield 'It mutates correctly when provided with a constant' => [
            <<<'PHP'
<?php


$a = preg_quote(\Class_With_Const::Const);
PHP
            ,
            <<<'PHP'
<?php

$a = \Class_With_Const::Const;
PHP
        ];

        yield 'It mutates correctly when a backslash is in front of the preg_quote' => [
            <<<'PHP'
<?php

$a = \preg_quote('bbbb');
PHP
            ,
            <<<'PHP'
<?php

$a = 'bbbb';
PHP
        ];

        yield 'It mutates correctly when preg_quote has a second parameter' => [
            <<<'PHP'
<?php

$a = preg_quote('bbbb','/');
PHP
            ,
            <<<'PHP'
<?php

$a = 'bbbb';
PHP
        ];

        yield 'It does not mutate other regex calls' => [
            <<<'PHP'
<?php

$a = preg_match('bbbb', '/');
PHP
        ];

        yield 'It does not mutate functions named preg_quote' => [
            <<<'PHP'
<?php

function preg_quote($text, $other)
{
}
PHP
        ];

        yield 'It mutates correctly within if statements' => [
            <<<'PHP'
<?php

$a = 'string';
if (preg_quote($a) === $a) {
    return true;
}
PHP
            ,
            <<<'PHP'
<?php

$a = 'string';
if ($a === $a) {
    return true;
}
PHP
        ];

        yield 'It mutates correctly when preg_quote is wrongly capitalized' => [
            <<<'PHP'
<?php

$a = PreG_qUotE('bbbb','/');
PHP
            ,
            <<<'PHP'
<?php

$a = 'bbbb';
PHP
        ];

        yield 'It mutates correctly when preg_quote uses another function as input' => [
            <<<'PHP'
<?php

$a = preg_quote($foo->bar(),'/');
PHP
            ,
            <<<'PHP'
<?php

$a = $foo->bar();
PHP
        ];

        yield 'It mutates correctly within a more complex situation' => [
            <<<'PHP'
<?php

function bar($input)
{
    return array_map(function ($key, $value) {
        return strtolower(preg_quote($key . (ctype_alnum($value) ? '' : $value), '/'));
    }, $input);
}
PHP
            ,
            <<<'PHP'
<?php

function bar($input)
{
    return array_map(function ($key, $value) {
        return strtolower($key . (ctype_alnum($value) ? '' : $value));
    }, $input);
}
PHP
        ];

        yield 'It does not mutate when the function name is a variable' => [
            <<<'PHP'
<?php

$b = 'preg_quote';
$a = $b($foo->bar(), '/');
PHP
        ];
    }
}
