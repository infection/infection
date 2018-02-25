<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\ZeroIteration;

use Infection\Mutator\Mutator;
use Infection\Mutator\ZeroIteration\Foreach_;
use Infection\Tests\Mutator\AbstractMutatorTestCase;

class ForeachTest extends AbstractMutatorTestCase
{
    protected function getMutator(): Mutator
    {
        return new Foreach_();
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
            'It mutates to new array in foreach' => [
                <<<'PHP'
<?php

$array = [1, 2];

foreach ($array as $value) {
}
PHP
                ,
                <<<'PHP'
<?php

$array = [1, 2];
foreach (array() as $value) {
}
PHP
                ,
            ],
        ];
    }
}
