<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ConditionalNegotiation;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class GreaterThanOrEqualToNegotiationTest extends AbstractMutatorTestCase
{
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
                <<<'PHP'
<?php

1 >= 1;
PHP
                ,
                <<<'PHP'
<?php

1 < 1;
PHP
                ,
            ],
        ];
    }
}
