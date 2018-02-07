<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Mutator\ConditionalNegotiation\Identical;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IdenticalTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Identical();
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
            'It mutates strict comparison' => [
                <<<'CODE'
<?php

1 === 1;
CODE
                ,
                <<<'CODE'
<?php

1 !== 1;
CODE
                ,
            ],
            'It does not mutate not strict comparison' => [
                <<<'CODE'
<?php

1 == 1;
CODE
                ,
            ],
            'It does not mutate not comparison' => [
                <<<'CODE'
<?php

1 !== 1;
CODE
                ,
            ],
        ];
    }
}
