<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\MinusEqual;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class MinusEqualTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new MinusEqual();
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
            'It mutates minus equals' => [
                <<<'PHP'
<?php

$a = 1;
$a -=2;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a += 2;
PHP
                ,
            ],
            'It does not mutate normal minus' => [
                <<<'PHP'
<?php

$a = 1;
$a = $a - 2;
PHP
                ,
            ],
        ];
    }
}
