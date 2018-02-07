<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Mutator\Boolean;

use Infection\Mutator\Boolean\LogicalLowerAnd;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LogicalLowerAndTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LogicalLowerAnd();
    }

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
            'It mutates logical lower and' => [
                <<<'CODE'
<?php

true and false;
CODE
                ,
                <<<'CODE'
<?php

true or false;
CODE
                ,
            ],
            'It does not mutate logical and' => [
                <<<'CODE'
<?php

true && false;
CODE
                ,
            ],
        ];
    }
}
