<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\OneZeroInteger;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class OneZeroIntegerTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new OneZeroInteger();
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
            'It mutates int one to zero' => [
                <<<'CODE'
<?php

10 + 1;
CODE
                ,
                <<<'CODE'
<?php

10 + 0;
CODE
                ,
            ],
            'It mutates int zero to one' => [
                <<<'CODE'
<?php

10 + 0;
CODE
                ,
                <<<'CODE'
<?php

10 + 1;
CODE
                ,
            ],
            'It does not mutate float zero to one' => [
                <<<'CODE'
<?php

10 + 0.0;
CODE
                ,
            ],
            'It does not mutate float one to zer0' => [
                <<<'CODE'
<?php

10 + 1.0;
CODE
                ,
            ],
        ];
    }
}
