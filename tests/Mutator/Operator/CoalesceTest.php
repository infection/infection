<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Operator;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class CoalesceTest extends AbstractMutatorTestCase
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
        yield 'Mutate coalesce with scalar values' => [
            <<<PHP
<?php

'value' ?? 'otherValue';
PHP
            ,
            <<<PHP
<?php

'otherValue';
PHP
        ];

        yield 'Mutate coalesce when left argument is variable' => [
            <<<'PHP'
<?php

$foo = 'value';
$foo ?? 'otherValue';
PHP
            ,
            <<<'PHP'
<?php

$foo = 'value';
'otherValue';
PHP
        ];

        yield 'Mutate coalesce with expression' => [
            <<<PHP
<?php

'value' . 'withConcat' ?? 'otherValue';
PHP
            ,
            <<<PHP
<?php

'otherValue';
PHP
        ];

        yield 'Mutate coalesce with expression as second param' => [
            <<<PHP
<?php

'value' ?? 'value' . 'withConcat';
PHP
            ,
            <<<PHP
<?php

'value' . 'withConcat';
PHP
        ];

        yield 'Mutate coalesce with variable as second argument' => [
            <<<'PHP'
<?php

$foo = 5;
'value' ?? $foo;
PHP
            ,
            <<<'PHP'
<?php

$foo = 5;
$foo;
PHP
        ];

        yield 'Mutate coalesce with variable as second argument' => [
            <<<'PHP'
<?php

if ('value' ?? 5) {
}
PHP
            ,
            <<<'PHP'
<?php

if (5) {
}
PHP
        ];
    }
}
