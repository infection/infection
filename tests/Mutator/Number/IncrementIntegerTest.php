<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Mutator\Mutator;
use Infection\Mutator\Number\IncrementInteger;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IncrementIntegerTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new IncrementInteger();
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
            'It increments an integer' => [
                <<<'CODE'
<?php

if ($foo < 10) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < 11) {
    echo 'bar';
}
CODE
                ,
            ],
            'It does not increment the number zero' => [
                <<<'CODE'
<?php

if ($foo < 0) {
    echo 'bar';
}
CODE
                ,
            ],
            'It increments one' => [
                <<<'CODE'
<?php

if ($foo < 1) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < 2) {
    echo 'bar';
}
CODE
                ,
            ],
            'It does not increment floats' => [
                <<<'CODE'
<?php

if ($foo < 1.0) {
    echo 'bar';
}
CODE
            ],
            'It decrements a negative integer' => [
                <<<'CODE'
<?php

if ($foo < -10) {
    echo 'bar';
}
CODE
                ,
                <<<'CODE'
<?php

if ($foo < -11) {
    echo 'bar';
}
CODE
                ,
            ],
        ];
    }
}
