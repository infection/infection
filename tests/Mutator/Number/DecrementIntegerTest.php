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
                <<<'PHP'
<?php

if ($foo < 10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < 9) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not decrement the number one' => [
                <<<'PHP'
<?php

if ($foo < 1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It decrements zero' => [
                <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -1) {
    echo 'bar';
}
PHP
                ,
            ],
            'It increments a negative integer' => [
                <<<'PHP'
<?php

if ($foo < -10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -9) {
    echo 'bar';
}
PHP
                ,
            ],
        ];
    }
}
