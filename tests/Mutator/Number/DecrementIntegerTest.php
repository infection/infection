<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class DecrementIntegerTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new DecrementInteger();
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
            'It decrements an integer' => [
                <<<'CODE'
<?php

if ($foo < 10) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < 9) {
    echo 'bar';
}
CODE
                ,
            ],
            'It does not decrement the number one' => [
                <<<'CODE'
<?php

if ($foo < 1) {
    echo 'bar';
}
CODE
                ,
            ],
            'It decrements zero' => [
                <<<'CODE'
<?php

if ($foo < 0) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < -1) {
    echo 'bar';
}
CODE
                ,
            ],
            'It increments a negative integer' => [
                <<<'CODE'
<?php

if ($foo < -10) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < -9) {
    echo 'bar';
}
CODE
                ,
            ],
        ];
    }
}
