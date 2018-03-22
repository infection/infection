<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Statement;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class AssignTest extends AbstractMutatorTestCase
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
        yield 'It mutates method calls assignments' => [
            <<<'PHP'
<?php

$a = $b;
$var->a = $b;
$var->a = count($a);
$var->b = $var->a;
$var->c = $var->foo();
$d = $var->foo();
if ($bar->baz && $foo->a = $baz->b()) {
}

PHP
            ,
            <<<'PHP'
<?php

true || ($a = $b);
true || ($var->a = $b);
true || ($var->a = count($a));
true || ($var->b = $var->a);
true || ($var->c = $var->foo());
true || ($d = $var->foo());
if ($bar->baz && (true || ($foo->a = $baz->b()))) {
}
PHP
            ,
        ];

        yield 'It does not mutate constant assignments' => [
            <<<'PHP'
<?php

$a = 1;
$var->b = false;
$foo->bar = [];
PHP
            ,
        ];
    }
}
