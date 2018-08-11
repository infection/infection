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
final class RoundingFamilyTest extends AbstractMutatorTestCase
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
        yield 'It mutates round() to floor() and ceil()' => [
            <<<'PHP'
<?php

$a = round(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$a = floor(1.23);
PHP
            ,
            <<<'PHP'
<?php

$a = ceil(1.23);
PHP
            ],
        ];

        yield 'It mutates floor() to round() and ceil()' => [
            <<<'PHP'
<?php

$a = floor(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$a = ceil(1.23);
PHP
                ,
                <<<'PHP'
<?php

$a = round(1.23);
PHP
            ],
        ];

        yield 'It mutates ceil() to round() and floor()' => [
            <<<'PHP'
<?php

$a = ceil(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$a = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$a = round(1.23);
PHP
            ],
        ];

        yield 'It mutates if function name is incorrectly cased' => [
            <<<'PHP'
<?php

$a = CeIl(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$a = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$a = round(1.23);
PHP
            ],
        ];

        yield 'It does not mutate if the function is a variable' => [
            <<<'PHP'
<?php

$foo = 'floor';
$foo(1.23);
PHP
        ];

        yield 'It mutates round() to floor() and ceil() and leaves only 1 argument' => [
            <<<'PHP'
<?php

$a = round(1.23, 2, PHP_ROUND_HALF_UP);
PHP
            ,
            [
                <<<'PHP'
<?php

$a = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$a = ceil(1.23);
PHP
            ],
        ];
    }
}
