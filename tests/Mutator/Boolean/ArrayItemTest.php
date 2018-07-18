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
final class ArrayItemTest extends AbstractMutatorTestCase
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
        yield 'It mutates double arrow operator to a greater than comparison when operands can have side-effects' => [
            <<<'PHP'
<?php

[$a->foo => $b->bar];
[$a->foo() => bar()];
[foo() => $b->bar];
[$foo => $b->bar];
PHP
            ,
            <<<'PHP'
<?php

[$a->foo > $b->bar];
[$a->foo() > bar()];
[foo() > $b->bar];
[$foo > $b->bar];
PHP
            ,
        ];

        yield 'It does not mutate arrays without double arrow operator' => [
            <<<'PHP'
<?php

[$b];
PHP
            ,
        ];

        yield 'It does not mutate arrays when side-effects are not expected' => [
            <<<'PHP'
<?php

['string' => 1];
[true => false];
[$a => $b];
PHP
            ,
        ];
    }
}
