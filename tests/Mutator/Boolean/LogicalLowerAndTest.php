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
                <<<'PHP'
<?php

true and false;
PHP
                ,
                <<<'PHP'
<?php

true or false;
PHP
                ,
            ],
            'It does not mutate logical and' => [
                <<<'PHP'
<?php

true && false;
PHP
                ,
            ],
        ];
    }
}
