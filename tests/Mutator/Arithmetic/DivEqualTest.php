<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Mutator\Arithmetic\DivEqual;
use Infection\Mutator\Mutator;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class DivEqualTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new DivEqual();
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
            'It changes divison equals' => [
                <<<'PHP'
<?php

$a = 1;
$a /=2;
PHP
                ,
                <<<'PHP'
<?php

$a = 1;
$a *= 2;
PHP
                ,
            ],
            'It does not change normal division' => [
                <<<'PHP'
<?php

$a = 10 / 2;
PHP
                ,
            ],
        ];
    }
}
