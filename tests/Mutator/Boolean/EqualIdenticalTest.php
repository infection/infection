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
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates identical operator into equal operator' => [
            <<<'PHP'
<?php

$a == $b;
(int) $c == 2;
$d == null;
false == strpos();
PHP
            ,
            <<<'PHP'
<?php

$a === $b;
(int) $c === 2;
$d === null;
false === strpos();
PHP
            ,
        ];
    }
}
