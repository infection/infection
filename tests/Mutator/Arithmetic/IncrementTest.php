<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Increment;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IncrementTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Increment();
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
            'It replaces post increment' => [
                <<<'CODE'
<?php

$a = 1; 
$a++;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
$a--;
CODE
                ,
            ],
            'It replaces pre increment' => [
                <<<'CODE'
<?php

$a = 1;
++$a;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
--$a;
CODE
                ,
            ],
            'It does not change when its not a real increment' => [
                <<<'CODE'
<?php

$b + +$a;
CODE
                ,
            ],
        ];
    }
}
