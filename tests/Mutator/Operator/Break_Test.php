<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class Break_Test extends AbstractMutatorTestCase
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
            'It replaces break with continue in while' => [
                <<<'PHP'
<?php

while (true) {
    break;
}
PHP
                ,
                <<<'PHP'
<?php

while (true) {
    continue;
}
PHP
                ,
            ],
            'It does not replaces break with continue in switch' => [
                <<<'PHP'
<?php

switch (1) {
    case 1:
        break;
}
PHP
                ,
            ],
        ];
    }
}
