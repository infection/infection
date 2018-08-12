<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Rounding;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 * @group f
 */
final class RoundTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider providesMutatorCases
     *
     * @param string $input
     * @param string|null $output
     */
    public function test_mutator(string $input, string $output = null): void
    {
        $this->doTest($input, $output);
    }

    public function providesMutatorCases(): \Generator
    {
        yield 'It mutates hard-coded floor to round' => [
            <<<'PHP'
<?php

floor(2.5);
PHP
            ,
            <<<'PHP'
<?php

round(2.5);
PHP
        ];

        yield 'It mutates hard-coded ceil to round' => [
            <<<'PHP'
<?php

ceil(2.5);
PHP
            ,
            <<<'PHP'
<?php

round(2.5);
PHP
        ];

        yield 'It mutates variables on ceil to round' => [
            <<<'PHP'
<?php

$float = '2.5';
ceil($float);
PHP
            ,
            <<<'PHP'
<?php

$float = '2.5';
round($float);
PHP
        ];

        yield 'It mutates variables on floor to round' => [
            <<<'PHP'
<?php

$float = '2.5';
ceil($float);
PHP
            ,
            <<<'PHP'
<?php

$float = '2.5';
round($float);
PHP
        ];

        yield 'It does not mutate other functions' => [
            <<<'PHP'
<?php

strtolower('lower');
PHP
        ];

        yield 'It mutates \ceil to round' => [
            <<<'PHP'
<?php

\ceil(9.9);
PHP
            ,
            <<<'PHP'
<?php

round(9.9);
PHP
        ];

        yield 'It mutates \floor to round' => [
            <<<'PHP'
<?php

\floor(9.9);
PHP
            ,
            <<<'PHP'
<?php

round(9.9);
PHP
        ];

        yield 'It mutates when ceil is being assigned to a variable' => [
            <<<'PHP'
<?php

$result = ceil(9.9);
PHP
            ,
            <<<'PHP'
<?php

$result = round(9.9);
PHP
        ];

        yield 'It mutates when floor is being assigned to a variable' => [
            <<<'PHP'
<?php

$result = floor(9.9);
PHP
            ,
            <<<'PHP'
<?php

$result = round(9.9);
PHP
        ];

        yield 'It mutates when the function is incorrectly cased' => [
            <<<'PHP'
<?php

$result = CeiL(9.9);
PHP
            ,
            <<<'PHP'
<?php

$result = round(9.9);
PHP
        ];

        yield 'It mutates when function is part of a control flow statement' => [
            <<<'PHP'
<?php

while (ceil(9.9)) {
}
PHP
            ,
            <<<'PHP'
<?php

while (round(9.9)) {
}
PHP
        ];
    }
}
