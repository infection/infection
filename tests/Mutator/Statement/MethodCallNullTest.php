<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Statement;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class MethodCallNullTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It removes method calls' => [
                <<<'PHP'
<?php

bar();
$a = bar();
$var->foo();
$var->foo() == 1;
$var->foo()->bar();
$var->foo()->bar()->baz();
$var->foo()->bar()->baz() == $var;
if ($bar || $var->foo()) {
}
if ($var->foo()->bar() == 1) {
}
PHP
                ,
                <<<'PHP'
<?php

null;
$a = null;
null;
null == 1;
null;
null;
null == $var;
if ($bar || null) {
}
if (null == 1) {
}
PHP
                ,
            ],
        ];
    }
}
