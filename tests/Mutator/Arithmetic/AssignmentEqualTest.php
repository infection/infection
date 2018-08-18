<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Arithmetic;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class AssignmentEqualTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): array
    {
        return [
            'It mutates a comparison to an assignment' => [
                <<<'PHP'
<?php

if ($a == $b) {
}
PHP
                ,
                <<<'PHP'
<?php

if ($a = $b) {
}
PHP
                ,
            ],
            'It does not mutate comparsion to an impossible assignment' => [
                        <<<'PHP'
<?php

if (1 == $a) {
}
PHP
                        ,
            ],
            'It does not try to assign a variable to a class constant' => [
                        <<<'PHP'
<?php

if (BaseClass::CLASS_CONST == $a) {
}
PHP
                        ,
            ],
            'It does not try to assign a variable to a built in constant' => [
                        <<<'PHP'
<?php

if (PHP_EOL == $a) {
}
PHP
                        ,
            ],
            'It does not try to assign a scalar to a result of a function call' => [
                        <<<'PHP'
<?php

if ($x->getFoo() == 1) {
}
PHP
                        ,
            ],
        ];
    }
}
