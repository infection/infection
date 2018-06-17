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

(unset) 'value' ?? 'otherValue';
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
(unset) $foo ?? 'otherValue';
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

(unset) ('value' . 'withConcat') ?? 'otherValue';
PHP
        ];
    }
}
