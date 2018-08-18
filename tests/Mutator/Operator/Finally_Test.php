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
final class Finally_Test extends AbstractMutatorTestCase
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
        yield 'It removes the finally statement' => [
            <<<'PHP'
<?php

try {
    $a = 1;
} catch (\Exception $e) {
    $a = 2;
} finally {
    $a = 3;
}
PHP
            ,
            <<<'PHP'
<?php

try {
    $a = 1;
} catch (\Exception $e) {
    $a = 2;
} 
PHP
            ,
        ];

        yield 'It does not mutate when no catch() blocks are present' => [
            <<<'PHP'
<?php

try {
    $a = 1;
} finally {
    $a = 2;
}
PHP
            ,
        ];
    }
}
