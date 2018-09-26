<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class EqualIdenticalTest extends AbstractMutatorTestCase
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
        yield 'It mutates with two variables' => [
            <<<'PHP'
<?php

$a == $b;
PHP
            ,
            <<<'PHP'
<?php

$a === $b;
PHP
            ,
        ];

        yield 'It mutates with a cast' => [
            <<<'PHP'
<?php

(int) $c == 2;
PHP
            ,
            <<<'PHP'
<?php

(int) $c === 2;
PHP
            ,
        ];

        yield 'It mutates with a constant' => [
            <<<'PHP'
<?php

$d == null;
PHP
            ,
            <<<'PHP'
<?php

$d === null;
PHP
            ,
        ];

        yield 'It mutates with a function' => [
            <<<'PHP'
<?php

false == strpos();
PHP
            ,
            <<<'PHP'
<?php

false === strpos();
PHP
            ,
        ];
    }
}
