<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\OneZeroFloat;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class OneZeroFloatTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new OneZeroFloat();
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
            'It mutates float one to zero' => [
                <<<'CODE'
<?php

10 + 1.0;
CODE
                ,
                <<<'CODE'
<?php

10 + 0.0;
CODE
                ,
            ],
            'It mutates float zero to one' => [
                <<<'CODE'
<?php

10 + 0.0;
CODE
                ,
                <<<'CODE'
<?php

10 + 1.0;
CODE
                ,
            ],
            'It does not mutate int zero to one' => [
                <<<'CODE'
<?php

10 + 0;
CODE
                ,
            ],
            'It does not mutate int one to zer0' => [
                <<<'CODE'
<?php

10 + 1;
CODE
                ,
            ],
        ];
    }
}
