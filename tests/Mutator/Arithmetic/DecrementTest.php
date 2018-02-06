<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class DecrementTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Decrement();
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
            'It replaces post decrement' => [
                <<<'CODE'
<?php

$a = 1; 
$a--;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
$a++;
CODE
                ,
            ],
            'It replaces pre decrement' => [
                <<<'CODE'
<?php

$a = 1;
--$a;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
++$a;
CODE
                ,
            ],
            'It does not change when its not a real decrement' => [
                <<<'CODE'
<?php

$b - -$a;
CODE
                ,
            ],
        ];
    }
}
