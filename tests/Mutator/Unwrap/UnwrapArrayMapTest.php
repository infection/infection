<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Unwrap;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class UnwrapArrayMapTest extends AbstractMutatorTestCase
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
        yield [
            <<<'PHP'
<?php

$a = array_map('strtolower', ['A', 'B', 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
PHP
        ];

        yield [
            <<<'PHP'
<?php

$a = array_map(function(string $letter): string {
    return strtolower($letter);
}, ['A', 'B', 'C']);
PHP
            ,
            <<<'PHP'
<?php

$a = ['A', 'B', 'C'];
PHP
        ];
    }
}
