<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Removal;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class FunctionCallRemovalTest extends AbstractMutatorTestCase
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
        yield 'It removes a function call without parameters' => [
            <<<'PHP'
<?php

foo();
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
            ,
        ];

        yield 'It removes a function call with parameters' => [
            <<<'PHP'
<?php

bar(3, 4);
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
            ,
        ];

        yield 'It removes dynamic function calls with string' => [
            <<<'PHP'
<?php

$start = true;
('foo')();
$end = true;

PHP
            ,
            <<<'PHP'
<?php

$start = true;

$end = true;
PHP
            ,
        ];

        yield 'It removes dynamic function call with variable' => [
            <<<'PHP'
<?php

$start = true;
$foo();
$end = true;

PHP
            ,
            <<<'PHP'
<?php

$start = true;

$end = true;
PHP
            ,
        ];

        yield 'It does not remove a function call that is assigned to something' => [
            <<<'PHP'
<?php

$b = foo();
$a = 3;
PHP
            ,
        ];

        yield 'It does not remove a function call within a statement' => [
            <<<'PHP'
<?php

if (foo()) {
    $a = 3;
}
while (foo()) {
    $a = 3;
}

PHP
            ,
        ];

        yield 'It does not remove a function call that is the parameter of another function or method' => [
            <<<'PHP'
<?php

$a = foo(3, bar());
PHP
        ];

        yield 'It does not remove a method call' => [
            <<<'PHP'
<?php

$this->foo();
$a = 3;
PHP
        ];

        yield 'It does not remove an assert() call' => [
            <<<'PHP'
<?php

assert(true === true);
aSsert(true === true);
\assert(true === true);
$a = 3;
PHP
        ];
    }
}
