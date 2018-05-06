<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class Continue_Test extends AbstractMutatorTestCase
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
            'It replaces continue with break in while' => [
                <<<'PHP'
<?php

while (true) {
    continue;
}
PHP
                ,
                <<<'PHP'
<?php

while (true) {
    break;
}
PHP
                ,
            ],
            'It does not replaces continue with break in switch' => [
                <<<'PHP'
<?php

switch (1) {
    case 1:
        continue;
}
PHP
                ,
            ],
        ];
    }
}
