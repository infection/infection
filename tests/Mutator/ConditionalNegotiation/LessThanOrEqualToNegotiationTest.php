<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Mutator\ConditionalNegotiation\LessThanOrEqualToNegotiation;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class LessThanOrEqualToNegotiationTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new LessThanOrEqualToNegotiation();
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
            'It mutates less than or equal to' => [
                <<<'PHP'
<?php

1 <= 1;
PHP
                ,
                <<<'PHP'
<?php

1 > 1;
PHP
                ,
            ],
        ];
    }
}
