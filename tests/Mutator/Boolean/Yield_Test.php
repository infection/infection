<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Boolean;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class Yield_Test extends AbstractMutatorTestCase
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
        yield 'It mutates a yield with a double arrow to a yield with a greater than comparison' => [
            <<<'PHP'
<?php

(yield $a => $b);
PHP
            ,
            <<<'PHP'
<?php

(yield $a > $b);
PHP
            ,
        ];

        yield 'It does not mutate yields without a double arrow operator' => [
            <<<'PHP'
<?php

(yield $b);
PHP
            ,
        ];
    }
}
