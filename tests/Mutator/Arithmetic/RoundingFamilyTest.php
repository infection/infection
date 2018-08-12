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

$var = round(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$var = floor(1.23);
PHP
            ,
            <<<'PHP'
<?php

$var = ceil(1.23);
PHP
            ],
        ];

        yield 'It mutates floor() to round() and ceil()' => [
            <<<'PHP'
<?php

$var = floor(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$var = ceil(1.23);
PHP
                ,
                <<<'PHP'
<?php

$var = round(1.23);
PHP
            ],
        ];

        yield 'It mutates ceil() to round() and floor()' => [
            <<<'PHP'
<?php

$var = ceil(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$var = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$var = round(1.23);
PHP
            ],
        ];

        yield 'It mutates if function name is incorrectly cased' => [
            <<<'PHP'
<?php

$var = CeIl(1.23);
PHP
            ,
            [
                <<<'PHP'
<?php

$var = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$var = round(1.23);
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

$var = round(1.23, 2, PHP_ROUND_HALF_UP);
PHP
            ,
            [
                <<<'PHP'
<?php

$var = floor(1.23);
PHP
                ,
                <<<'PHP'
<?php

$var = ceil(1.23);
PHP
            ],
        ];

        yield 'It does not mutate other functions' => [
            <<<'PHP'
<?php
 strtolower('lower');
PHP
        ];

        yield 'It mutates \ceil() to round() and floor()' => [
            <<<'PHP'
<?php

$float = 1.23;
return \ceil($float);
PHP
            ,
            [
                <<<'PHP'
<?php

$float = 1.23;
return floor($float);
PHP
                ,
                <<<'PHP'
<?php

$float = 1.23;
return round($float);
PHP
            ],
        ];

        yield 'It mutates \floor() to round() and ceil() in a control flow statement' => [
            <<<'PHP'
<?php

while (\floor(1.23)) {
}
PHP
            ,
            [
                <<<'PHP'
<?php

while (ceil(1.23)) {
}
PHP
                ,
                <<<'PHP'
<?php

while (round(1.23)) {
}
PHP
            ],
        ];

        yield 'It mutates ceil() to round() and floor() while assigning inside the function call' => [
            <<<'PHP'
<?php

echo ceil($result = $this->average());
PHP
            ,
            [
                <<<'PHP'
<?php

echo floor($result = $this->average());
PHP
                ,
                <<<'PHP'
<?php

echo round($result = $this->average());
PHP
            ],
        ];

        yield 'It mutates round() to ceil() and floor() during arithmetic operations' => [
            <<<'PHP'
<?php

return round($this->positive / $this->total);
PHP
            ,
            [
                <<<'PHP'
<?php

return floor($this->positive / $this->total);
PHP
                ,
                <<<'PHP'
<?php

return ceil($this->positive / $this->total);
PHP
            ],
        ];
    }
}
