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
final class MethodRemovalTest extends AbstractMutatorTestCase
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
        yield 'It removes a method call' => [
            <<<'PHP'
<?php

$this->foo();
$foo->bar(3, 4);
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
            ,
        ];

        yield 'It remove a static method call' => [
            <<<'PHP'
<?php

self::foo();
THatClass::bar(3, 4);
$a = 3;
PHP
            ,
            <<<'PHP'
<?php

$a = 3;
PHP
        ];

        yield 'It does not remove a method call that is assigned to something' => [
            <<<'PHP'
<?php

$b = foo();
$a = 3;
PHP
            ,
        ];

        yield 'It does not remove a method call within a statement' => [
            <<<'PHP'
<?php

if ($this->foo()) {
    $a = 3;
}
while ($foo->foo()) {
    $a = 3;
}

PHP
            ,
        ];

        yield 'It does not remove a method call that is the parameter of another function or method' => [
            <<<'PHP'
<?php

$a = $this->foo(3, $a->bar());
PHP
        ];

        yield 'It does not remove a function call' => [
            <<<'PHP'
<?php

foo();
$a = 3;
PHP
        ];
    }
}
