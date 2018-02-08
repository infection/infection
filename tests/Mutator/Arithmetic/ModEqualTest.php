<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\ModEqual;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ModEqualTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new ModEqual();
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
            'It mutates mod equal' => [
                <<<'PHP'
<?php

$a = 1;
$a %= 2;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a *= 2;
PHP
                ,
            ],
            'It does not mutate normal mod' => [
                <<<'PHP'
<?php

$a = 10 % 3;
PHP
                ,
            ],
        ];
    }
}
