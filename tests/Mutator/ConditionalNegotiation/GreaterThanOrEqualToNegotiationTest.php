<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Mutator\ConditionalNegotiation\GreaterThanOrEqualToNegotiation;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class GreaterThanOrEqualToNegotiationTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new GreaterThanOrEqualToNegotiation();
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
            'It mutates greater than or equal to' => [
                <<<'CODE'
<?php

1 >= 1;
CODE
                ,
                <<<'CODE'
<?php

1 < 1;
CODE
                ,
            ],
        ];
    }
}
