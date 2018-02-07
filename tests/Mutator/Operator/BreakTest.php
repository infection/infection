<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Mutator\Mutator;
use Infection\Mutator\Operator\Break_;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class BreakTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Break_();
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
            'It replaces break with continue in while' => [
                <<<'CODE'
<?php

while (true) {
    break;
}
CODE
                ,
                <<<'CODE'
<?php

while (true) {
    continue;
}
CODE
                ,
            ],
            'It does not replaces break with continue in switch' => [
                <<<'CODE'
<?php

switch (1) {
    case 1:
        break;
}
CODE
                ,
            ],
        ];
    }
}
