<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Number;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

class IncrementIntegerTest extends AbstractMutatorTestCase
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
            'It increments an integer' => [
                <<<'PHP'
<?php

if ($foo < 10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < 11) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not increment the number zero' => [
                <<<'PHP'
<?php

if ($foo < 0) {
    echo 'bar';
}
PHP
                ,
            ],
            'It increments one' => [
                <<<'PHP'
<?php

if ($foo < 1) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < 2) {
    echo 'bar';
}
PHP
                ,
            ],
            'It does not increment floats' => [
                <<<'PHP'
<?php

if ($foo < 1.0) {
    echo 'bar';
}
PHP
            ],
            'It decrements a negative integer' => [
                <<<'PHP'
<?php

if ($foo < -10) {
    echo 'bar';
}
PHP
                ,
                <<<'PHP'
<?php

if ($foo < -11) {
    echo 'bar';
}
PHP
                ,
            ],
        ];
    }
}
