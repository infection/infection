<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class AssignmentEqualTest extends AbstractMutatorTestCase
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
            'It mutates a comparison to an assignment' => [
                <<<'PHP'
<?php

if ($a == $b) {
}
PHP
                ,
                <<<'PHP'
<?php

if ($a = $b) {
}
PHP
                ,
            ],
            'It does not mutate comparsion to an impossible assignment' => [
                        <<<'PHP'
<?php

if (1 == $a) {
}
PHP
                        ,
                ],
        ];
    }
}
