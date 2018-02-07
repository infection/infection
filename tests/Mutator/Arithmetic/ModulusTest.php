<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\Modulus;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ModulusTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Modulus();
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
            'It mutates normal mod' => [
                <<<'CODE'
<?php

$a = 10 % 3;
CODE
                ,
                <<<'CODE'
<?php

$a = 10 * 3;
CODE
                ,
            ],
            'It does not mutate mod equals' => [
                <<<'CODE'
<?php

$a = 1;
$a %= 2;
CODE
                ,
            ],
        ];
    }
}
