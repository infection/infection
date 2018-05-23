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
final class Foreach_Test extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null)
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates to new array in foreach' => [
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
        ];
        yield 'It does not change whether items were passed by reference' => [
            <<<'PHP'
<?php

$array = [1, 2];
foreach ($array as $key => &$value) {
    echo $value;
}
PHP
            ,
            <<<'PHP'
<?php

$array = [1, 2];
foreach (array() as $key => &$value) {
    echo $value;
}
PHP
        ];
    }
}
