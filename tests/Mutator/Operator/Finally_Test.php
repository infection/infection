<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class Finally_Test extends AbstractMutatorTestCase
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
            'It removes the finally statement' => [
                <<<'PHP'
<?php

try {
    $a = 1;
} catch (\Exception $e) {
    $a = 2;
} finally {
    $a = 3;
}
PHP
                ,
                <<<'PHP'
<?php

try {
    $a = 1;
} catch (\Exception $e) {
    $a = 2;
} 
PHP
                ,
            ],
        ];
    }
}
