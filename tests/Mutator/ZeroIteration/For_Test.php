<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ZeroIteration;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class For_Test extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates to false in for loop condition' => [
            <<<'PHP'
<?php

$array = [1, 2];

for($i = 0; $i < count($array); $i++) {
}
PHP
            ,
            <<<'PHP'
<?php

$array = [1, 2];
for ($i = 0; false; $i++) {
}
PHP
            ,
        ];

        yield 'It does not mutate the body of the for loop' => [
            <<<'PHP'
<?php

$array = [1, 2];
for($i = 0; $i < count($array); $i++) {
    if ($i == 1) {
        echo '$i is one';
    }
}
PHP
            ,
            <<<'PHP'
<?php

$array = [1, 2];
for ($i = 0; false; $i++) {
    if ($i == 1) {
        echo '$i is one';
    }
}
PHP
            ,
        ];
    }
}
