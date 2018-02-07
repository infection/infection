<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\MulEqual;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class MulEqualTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new MulEqual();
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
            'It mutates multiply equal' => [
                <<<'CODE'
<?php

$a = 1;
$a *= 2;
CODE
                ,
                <<<'CODE'
<?php

$a = 1;
$a /= 2;
CODE
                ,
            ],
            'It does not mutate normal multiply' => [
                <<<'CODE'
<?php

$a = 10 * 3;
CODE
                ,
            ],
        ];
    }
}
